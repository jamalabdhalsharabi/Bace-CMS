# تحليل عمليات التصنيفات والوسوم (Taxonomy Operations)

## 📋 نظرة عامة
التصنيفات تشمل الفئات (Categories)، الوسوم (Tags)، والتصنيفات المخصصة. تدعم الهيكل الهرمي والتعدد اللغوي.

---

## 🔄 State Machine Diagram

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│  active  │
└──────────┘            └──────────┘
                              │
                    ┌─────────┼─────────┐
                    ▼         ▼         ▼
             ┌──────────┐ ┌────────┐ ┌──────────────┐
             │ inactive │ │ merged │ │ soft_deleted │
             └──────────┘ └────────┘ └──────────────┘
                    │
                    ▼
             ┌──────────┐
             │  active  │
             └──────────┘
```

---

## 📌 العملية 1: إنشاء تصنيف (Create Category/Tag)

### 1. اسم العملية
`taxonomy.create`

### 2. الهدف
إنشاء تصنيف أو وسم جديد في النظام.

### 3. الشروط المسبقة
- صلاحية `taxonomy.create`
- الـ slug فريد ضمن نوع التصنيف
- الأب موجود (إذا كان فرعي)

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate slug uniqueness per type
    ├── Validate parent exists (if child)
    ├── Check max depth not exceeded
    └── Validate no circular reference

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Calculate hierarchy data
    │   ├── depth level
    │   ├── path (materialized path)
    │   └── order position
    │
    [5] Create Taxonomy Record
    │   └── INSERT INTO taxonomies (id, type, parent_id, slug, depth, path, order, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO taxonomy_translations (taxonomy_id, locale, name, description, ...)
    │
    [7] Process Icon/Image
    │
COMMIT TRANSACTION ───────────────────────────────────

[8] Dispatch Events
    └── TaxonomyCreated event

[9] Queue Jobs
    ├── InvalidateTaxonomyCacheJob
    └── RebuildTreeCacheJob
```

### 5. الآثار الجانبية
- إنشاء سجل التصنيف
- تحديث شجرة التصنيفات
- إبطال cache

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Duplicate Slug | Return 422 + suggestion |
| Parent Not Found | Return 422 |
| Max Depth Exceeded | Return 422 |
| Circular Reference | Return 422 |

### 7. API Endpoint

```http
POST /api/v1/taxonomies
{
  "type": "category",
  "parent_id": "uuid",
  "slug": "web-development",
  "translations": {
    "ar": { "name": "تطوير الويب", "description": "..." },
    "en": { "name": "Web Development", "description": "..." }
  },
  "icon": "code",
  "color": "#3B82F6",
  "meta": {
    "show_in_menu": true,
    "featured": false
  }
}
```

### 8. Webhook Payload

```json
{
  "event": "taxonomy.created",
  "payload": {
    "id": "uuid",
    "type": "category",
    "slug": "web-development",
    "parent_id": "uuid",
    "depth": 2
  }
}
```

---

## 📌 العملية 2: تعديل تصنيف (Update Taxonomy)

### 1. اسم العملية
`taxonomy.update`

### 2. خطوات التنفيذ

```
[1] Load taxonomy with lock

[2] Validate changes
    ├── Check slug uniqueness (if changed)
    └── Check parent change validity

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] If parent changed:
    │   ├── Validate no circular reference
    │   ├── Recalculate path and depth
    │   └── Update all descendants' paths
    │
    [5] Update Taxonomy Record
    │
    [6] Sync Translations
    │
COMMIT TRANSACTION ───────────────────────────────────

[7] Queue Jobs
    ├── InvalidateTaxonomyCacheJob
    ├── RebuildTreeCacheJob (if parent changed)
    └── UpdateContentUrlsJob (if slug changed)
```

---

## 📌 العملية 3: نقل في الشجرة (Move in Tree)

```http
PUT /api/v1/taxonomies/{id}/move
{
  "parent_id": "new-parent-uuid",
  "position": 2
}
```

**خطوات التنفيذ:**
```
[1] Validate move
    ├── Not moving to descendant
    ├── Not exceeding max depth
    └── Position valid

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update parent_id
    │
    [4] Recalculate depth for self and descendants
    │
    [5] Rebuild materialized paths
    │
    [6] Reorder siblings
    │
COMMIT ───────────────────────────────────────────────

[7] Queue RebuildTreeCacheJob
```

---

## 📌 العملية 4: إعادة ترتيب (Reorder)

```http
PUT /api/v1/taxonomies/reorder
{
  "parent_id": "uuid",
  "order": ["uuid1", "uuid2", "uuid3"]
}
```

---

## 📌 العملية 5: دمج تصنيفين (Merge Taxonomies)

```http
POST /api/v1/taxonomies/{id}/merge
{
  "merge_into": "target-uuid",
  "delete_source": true
}
```

**خطوات التنفيذ:**
```
[1] Validate both exist and same type

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Move all content from source to target
    │   └── UPDATE content_taxonomy SET taxonomy_id = target WHERE taxonomy_id = source
    │
    [4] Move children to target (or delete)
    │
    [5] If delete_source:
    │   └── Soft delete source taxonomy
    │   
    [6] Else:
    │   └── Mark as merged, store target reference
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── UpdateContentCountsJob
    ├── InvalidateCacheJob
    └── CreateRedirectJob (from source slug to target)
```

---

## 📌 العملية 6: تعطيل/تفعيل (Enable/Disable)

```http
POST /api/v1/taxonomies/{id}/toggle-status
{
  "status": "inactive"
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status
    │
    [3] If deactivating:
    │   └── Option to cascade to children
    │
COMMIT ───────────────────────────────────────────────

[4] Queue InvalidateCacheJob
```

---

## 📌 العملية 7: حذف تصنيف (Delete Taxonomy)

```http
DELETE /api/v1/taxonomies/{id}
{
  "handle_content": "move_to_parent",
  "handle_children": "move_up"
}
```

**خيارات التعامل مع المحتوى:**
- `move_to_parent`: نقل المحتوى للأب
- `move_to`: نقل لتصنيف محدد
- `remove`: إزالة الارتباط فقط

**خيارات التعامل مع الأبناء:**
- `move_up`: نقل للأب
- `delete`: حذف مع الأبناء
- `move_to`: نقل لتصنيف محدد

**خطوات التنفيذ:**
```
[1] Count affected content and children

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Handle content based on option
    │
    [4] Handle children based on option
    │
    [5] Soft delete taxonomy
    │
    [6] Create redirect (if needed)
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── RebuildTreeCacheJob
    ├── UpdateContentCountsJob
    └── InvalidateCacheJob
```

---

## 📌 العملية 8: استيراد تصنيفات (Import Taxonomies)

```http
POST /api/v1/taxonomies/import
{
  "type": "category",
  "data": [
    { "name": "...", "slug": "...", "parent_slug": "..." }
  ],
  "mode": "merge"
}
```

**أوضاع الاستيراد:**
- `replace`: استبدال الكل
- `merge`: دمج مع الموجود
- `skip_existing`: تخطي الموجود

---

## 📌 العملية 9: تصدير تصنيفات (Export Taxonomies)

```http
GET /api/v1/taxonomies/export?type=category&format=json
```

---

## 📌 العملية 10: إحصائيات المحتوى (Content Stats)

```http
GET /api/v1/taxonomies/{id}/stats
```

**Response:**
```json
{
  "total_content": 45,
  "by_type": {
    "article": 30,
    "product": 15
  },
  "children_count": 5,
  "descendants_count": 12
}
```

---

## 📌 العملية 11: إنشاء Taxonomy Type مخصص

```http
POST /api/v1/taxonomy-types
{
  "slug": "skill",
  "name": { "ar": "المهارات", "en": "Skills" },
  "hierarchical": false,
  "applies_to": ["project", "service"],
  "settings": {
    "max_depth": 1,
    "allow_multiple": true
  }
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                    Taxonomy Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin            System           Content         Cache        │
│    │                │                │               │           │
│    │── Create ─────▶│                │               │           │
│    │   (parent)     │                │               │           │
│    │                │                │               │           │
│    │── Create ─────▶│                │               │           │
│    │   (child)      │── Rebuild ─────────────────────▶│           │
│    │                │   Tree         │               │           │
│    │                │                │               │           │
│    │                │       ◀── Assign ──            │           │
│    │                │                │               │           │
│    │── Move ───────▶│                │               │           │
│    │                │── Update ─────▶│               │           │
│    │                │   Paths        │               │           │
│    │                │── Invalidate ──────────────────▶│           │
│    │                │                │               │           │
│    │── Merge ──────▶│                │               │           │
│    │                │── Migrate ────▶│               │           │
│    │                │   Content      │               │           │
│    │                │                │               │           │
│    │── Delete ─────▶│                │               │           │
│    │                │── Handle ─────▶│               │           │
│    │                │   Content      │               │           │
│    │                │── Rebuild ─────────────────────▶│           │
│    │                │                │               │           │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات التصنيفات**
