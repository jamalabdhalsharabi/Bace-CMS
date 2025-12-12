# تحليل عمليات القوائم (Menu/Navigation Operations)

## 📋 نظرة عامة
القوائم هي هياكل ملاحية تنظم روابط الموقع. تدعم التداخل المتعدد المستويات، الروابط الديناميكية، والتعدد اللغوي.

---

## 🔄 State Machine Diagram

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│  draft   │
└──────────┘            └──────────┘
                              │
                              ▼
                        ┌───────────┐
                        │ published │
                        └───────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌──────────────┐
       │ unpublished│   │ archived │   │ soft_deleted │
       └───────────┘   └───────────┘   └──────────────┘
```

---

## 📌 العملية 1: إنشاء قائمة (Create Menu)

### 1. اسم العملية
`menu.create`

### 2. الهدف
إنشاء قائمة ملاحية جديدة.

### 3. الشروط المسبقة
- صلاحية `menu.create`
- الموقع (location) متاح

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate location not already assigned
    └── Validate slug uniqueness

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Create Menu Record
    │   └── INSERT INTO menus (id, slug, location, status, ...)
    │
    [5] Create Translation Records
    │   └── INSERT INTO menu_translations (menu_id, locale, name, ...)
    │
COMMIT TRANSACTION ───────────────────────────────────

[6] Dispatch MenuCreated event
```

### 5. API Endpoint

```http
POST /api/v1/menus
{
  "slug": "main-menu",
  "location": "header",
  "translations": {
    "ar": { "name": "القائمة الرئيسية" },
    "en": { "name": "Main Menu" }
  }
}
```

---

## 📌 العملية 2: إضافة عنصر (Add Menu Item)

### 1. اسم العملية
`menu.add_item`

### 2. الهدف
إضافة عنصر جديد للقائمة.

### 3. أنواع العناصر
- `page`: رابط لصفحة
- `article`: رابط لمقال
- `category`: رابط لتصنيف
- `custom`: رابط مخصص
- `placeholder`: عنصر بدون رابط (للعناوين)

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate linked entity exists (if not custom)
    ├── Validate parent item exists (if child)
    └── Check max depth not exceeded

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Calculate position
    │   └── Get max order in parent + 1
    │
    [5] Create Menu Item Record
    │   └── INSERT INTO menu_items (id, menu_id, parent_id, type, entity_type, entity_id, url, target, order, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO menu_item_translations (item_id, locale, label, title, ...)
    │
COMMIT TRANSACTION ───────────────────────────────────

[7] Queue InvalidateMenuCacheJob
```

### 5. API Endpoint

```http
POST /api/v1/menus/{menu_id}/items
{
  "type": "page",
  "entity_id": "page-uuid",
  "parent_id": null,
  "translations": {
    "ar": { "label": "من نحن", "title": "تعرف علينا" },
    "en": { "label": "About Us", "title": "Learn about us" }
  },
  "icon": "info",
  "target": "_self",
  "css_class": "highlight"
}
```

**للرابط المخصص:**
```json
{
  "type": "custom",
  "url": "https://external.com",
  "target": "_blank",
  "translations": {
    "ar": { "label": "رابط خارجي" }
  }
}
```

---

## 📌 العملية 3: تعديل عنصر (Update Menu Item)

```http
PUT /api/v1/menus/{menu_id}/items/{item_id}
{
  "translations": {
    "ar": { "label": "عنوان جديد" }
  },
  "icon": "new-icon"
}
```

---

## 📌 العملية 4: نقل عنصر (Move Item)

```http
PUT /api/v1/menus/{menu_id}/items/{item_id}/move
{
  "parent_id": "new-parent-uuid",
  "position": 2
}
```

**خطوات التنفيذ:**
```
[1] Validate move
    ├── Not moving to descendant
    └── Max depth not exceeded

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update parent_id
    │
    [4] Reorder siblings at old position
    │
    [5] Insert at new position
    │
    [6] Reorder siblings at new position
    │
COMMIT ───────────────────────────────────────────────

[7] Queue InvalidateMenuCacheJob
```

---

## 📌 العملية 5: إعادة ترتيب العناصر (Reorder Items)

```http
PUT /api/v1/menus/{menu_id}/reorder
{
  "items": [
    { "id": "uuid1", "parent_id": null, "order": 0 },
    { "id": "uuid2", "parent_id": null, "order": 1 },
    { "id": "uuid3", "parent_id": "uuid1", "order": 0 }
  ]
}
```

**خطوات التنفيذ:**
```
[1] Validate all items belong to menu

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] For each item:
    │   └── UPDATE parent_id, order
    │
COMMIT ───────────────────────────────────────────────

[4] Queue InvalidateMenuCacheJob
```

---

## 📌 العملية 6: حذف عنصر (Delete Item)

```http
DELETE /api/v1/menus/{menu_id}/items/{item_id}
{
  "handle_children": "move_up"
}
```

**خيارات:**
- `move_up`: نقل الأبناء للأب
- `delete`: حذف مع الأبناء

---

## 📌 العملية 7: نشر القائمة (Publish Menu)

```http
POST /api/v1/menus/{id}/publish
```

**خطوات التنفيذ:**
```
[1] Validate menu has items

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at
    │
    [5] If replacing existing in location:
    │   └── Unpublish old menu
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs (CRITICAL)
    ├── InvalidateMenuCacheJob
    ├── WarmMenuCacheJob
    └── InvalidateCDNCacheJob
```

---

## 📌 العملية 8: تعيين موقع (Assign Location)

```http
PUT /api/v1/menus/{id}/location
{
  "location": "footer"
}
```

**المواقع الشائعة:**
- `header`: القائمة الرئيسية
- `footer`: قائمة الفوتر
- `sidebar`: القائمة الجانبية
- `mobile`: قائمة الجوال

---

## 📌 العملية 9: استنساخ القائمة (Clone Menu)

```http
POST /api/v1/menus/{id}/clone
{
  "new_slug": "menu-copy",
  "include_items": true
}
```

---

## 📌 العملية 10: مزامنة الروابط (Sync Links)

```
[Scheduled Job - Daily]

[1] For each menu item linked to entity:
    │
    [2] Check entity still exists
    │
    [3] Check entity still published
    │
    [4] If deleted or unpublished:
    │   ├── Mark item as broken
    │   └── Notify admin
    │
    [5] Update cached URL if slug changed
```

---

## 📌 العملية 11: معاينة القائمة (Preview Menu)

```http
GET /api/v1/menus/{id}/preview?locale=ar
```

**Response:**
```json
{
  "items": [
    {
      "id": "uuid",
      "label": "الرئيسية",
      "url": "/",
      "target": "_self",
      "children": [
        { "id": "uuid", "label": "...", "url": "..." }
      ]
    }
  ]
}
```

---

## 📌 العملية 12: تصدير/استيراد

```http
GET /api/v1/menus/{id}/export

POST /api/v1/menus/import
{
  "menu": {...},
  "items": [...]
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                      Menu Lifecycle Flow                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin            System           Cache           Frontend     │
│    │                │                │                │          │
│    │── Create ─────▶│                │                │          │
│    │                │                │                │          │
│    │── Add Items ──▶│                │                │          │
│    │── Reorder ────▶│                │                │          │
│    │                │                │                │          │
│    │── Preview ────▶│                │                │          │
│    │◀── Render ─────│                │                │          │
│    │                │                │                │          │
│    │── Publish ────▶│                │                │          │
│    │                │── Invalidate ──▶│                │          │
│    │                │── Warm ────────▶│                │          │
│    │                │                │                │          │
│    │                │                │       ◀── Get ─│          │
│    │                │                │── Serve ──────▶│          │
│    │                │                │                │          │
│    │                │◀── Sync Check ─│                │          │
│    │◀── Broken Link─│                │                │          │
│    │                │                │                │          │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات القوائم**
