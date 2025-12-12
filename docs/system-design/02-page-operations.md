# تحليل عمليات الصفحات الثابتة (Page Operations)

## 📋 نظرة عامة
الصفحات الثابتة هي محتوى دائم يمثل الهيكل الأساسي للموقع (مثل: من نحن، اتصل بنا، سياسة الخصوصية). تدعم القوالب المخصصة والتعدد اللغوي.

---

## 🔄 State Machine Diagram

```
┌──────────┐    save     ┌──────────┐   submit    ┌─────────────────┐
│  (new)   │────────────▶│  draft   │────────────▶│ pending_review  │
└──────────┘             └──────────┘             └─────────────────┘
                              ▲                          │
                              │                    ┌─────┴─────┐
                              │                    ▼           ▼
                         ┌────────┐          ┌──────────┐ ┌──────────┐
                         │rejected│◀─────────│in_review │ │ approved │
                         └────────┘          └──────────┘ └──────────┘
                                                               │
                              ┌─────────────────┬──────────────┘
                              ▼                 ▼
                        ┌───────────┐    ┌───────────┐
                        │ scheduled │    │ published │
                        └───────────┘    └───────────┘
                              │                │
                              └───────┬────────┘
                                      ▼
                              ┌─────────────┐
                              │ unpublished │
                              └─────────────┘
                                      │
                                      ▼
                              ┌───────────┐
                              │ archived  │
                              └───────────┘
```

---

## 📌 العملية 1: إنشاء صفحة (Create Page)

### 1. اسم العملية
`page.create`

### 2. الهدف
إنشاء صفحة ثابتة جديدة مع دعم القوالب والتعدد اللغوي.

### 3. الشروط المسبقة
- المستخدم مصادق ولديه صلاحية `page.create`
- القالب المختار موجود وصالح
- الـ slug فريد

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate slug uniqueness per locale
    ├── Validate template exists
    └── Validate parent page exists (if hierarchical)

[2] Authorization Check
    └── Gate::authorize('page.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Page Record
    │   └── INSERT INTO pages (id, template, parent_id, order, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO page_translations (page_id, locale, title, slug, content, ...)
    │
    [7] Create Initial Revision
    │
    [8] Process Page Sections/Blocks
    │   └── INSERT INTO page_sections (page_id, block_type, order, data)
    │
    [9] Process Media Attachments
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Dispatch Events
     ├── PageCreated event
     └── SitemapNeedsUpdate event

[11] Queue Jobs
     ├── InvalidateCacheJob
     └── UpdateNavigationCacheJob
```

### 5. الآثار الجانبية
- إنشاء سجل الصفحة
- تحديث شجرة الصفحات (إذا كانت فرعية)
- إبطال cache القوائم والـ sitemap

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Duplicate Slug | Return 422 + suggest alternative |
| Invalid Template | Return 422 + available templates |
| Parent Not Found | Return 422 |
| Circular Reference | Return 422 |

### 7. Idempotency & Concurrency
- استخدام `X-Idempotency-Key`
- فحص الـ slug قبل الإنشاء
- قفل الصفحة الأم خلال الإنشاء

### 8. Security Considerations
- تنظيف HTML من scripts خبيثة
- التحقق من صلاحيات القالب
- منع إنشاء صفحات في مسارات محجوزة (`/admin`, `/api`)

### 9. Observability

```yaml
metrics:
  - page.create.count
  - page.create.duration_ms
  - page.create.by_template

logs:
  level: info
  fields:
    - template: {template_name}
    - parent_id: {parent_id}
    - has_sections: {boolean}
```

### 10. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Editor | ✅ |
| Author | ✅ (non-system pages) |

### 11. API Endpoint

```http
POST /api/v1/pages
Authorization: Bearer {token}
Content-Type: application/json

{
  "template": "default",
  "parent_id": null,
  "translations": {
    "ar": { "title": "من نحن", "slug": "about-us", "content": "..." },
    "en": { "title": "About Us", "slug": "about-us", "content": "..." }
  },
  "sections": [
    { "type": "hero", "data": {...} },
    { "type": "content", "data": {...} }
  ]
}
```

### 12. Webhook Payload

```json
{
  "event": "page.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "template": "default",
    "path": "/about-us",
    "is_child": false
  }
}
```

---

## 📌 العملية 2: تعديل صفحة (Update Page)

### 1. اسم العملية
`page.update`

### 2. الهدف
تحديث محتوى أو إعدادات الصفحة.

### 3. الشروط المسبقة
- الصفحة موجودة
- الحالة تسمح بالتعديل
- صلاحية `page.update`
- ليست صفحة نظام محمية (أو لديه صلاحية خاصة)

### 4. خطوات التنفيذ

```
[1] Load page with lock

[2] Check if system page
    └── If system page, require elevated permission

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Create Revision
    │
    [5] Update Page Record
    │   └── Handle slug change (redirects)
    │
    [6] Sync Translations
    │
    [7] Sync Sections/Blocks
    │   ├── Update existing
    │   ├── Create new
    │   └── Delete removed
    │
    [8] Update hierarchy if parent changed
    │   └── Recalculate paths for children
    │
COMMIT TRANSACTION ───────────────────────────────────

[9] Queue Jobs
    ├── InvalidatePageCacheJob
    ├── UpdateNavigationCacheJob
    └── CreateRedirectJob (if slug changed)
```

### 5. الآثار الجانبية
- تحديث مسارات الصفحات الفرعية (إذا تغير الـ slug)
- إنشاء redirect من المسار القديم
- تحديث القوائم المرتبطة

### 6. العمليات الخاصة بالصفحات

#### 6.1 تغيير القالب (Change Template)
```
[1] Validate new template compatibility

[2] Map existing sections to new template
    └── Preserve compatible sections, warn about incompatible

[3] Update template reference

[4] Trigger section revalidation
```

#### 6.2 نقل في الشجرة (Move in Hierarchy)
```
[1] Validate new parent
    ├── Check not moving to descendant
    └── Check permissions on target

[2] BEGIN TRANSACTION
    │
    [3] Update parent_id
    │
    [4] Recalculate order
    │
    [5] Update paths for all descendants
    │
COMMIT

[6] Batch update redirects
```

---

## 📌 العملية 3: حفظ مسودة (Save Draft)

### 1. اسم العملية
`page.save_draft`

### 2. الهدف
حفظ تغييرات دون التأثير على النسخة المنشورة.

### 3. خطوات التنفيذ

```
[1] Validate partial data

[2] Check if page has published version
    └── If yes, save to draft_content column (separate from published)

[3] BEGIN TRANSACTION ─────────────────────────────────
    │
    [4] Update draft data
    │
    [5] Create auto-save revision
    │
COMMIT ───────────────────────────────────────────────

[6] Return preview URL
```

### 4. Implementation Notes
- الصفحات المنشورة تحتفظ بنسختين: `published_content` و `draft_content`
- Preview URL تعرض نسخة المسودة

---

## 📌 العملية 4-7: دورة المراجعة (Review Cycle)

*نفس نمط الخدمات مع الاختلافات التالية:*

### اختلافات خاصة بالصفحات:
- **System Pages**: الصفحات الأساسية (الرئيسية، 404، إلخ) لا تمر بمراجعة
- **Legal Pages**: صفحات قانونية (سياسة الخصوصية) تتطلب مراجعة قانونية إضافية
- **Critical Path**: صفحات في navigation رئيسي تتطلب موافقة Admin

---

## 📌 العملية 8: النشر (Publish Page)

### 1. اسم العملية
`page.publish`

### 2. الهدف
نشر الصفحة وإتاحتها للزوار.

### 3. خطوات التنفيذ

```
[1] Validate publishable

[2] BEGIN TRANSACTION ─────────────────────────────────
    │
    [3] Copy draft_content to published_content
    │
    [4] Update status → published
    │
    [5] Set published_at, published_by
    │
    [6] Clear draft_content
    │
    [7] Create Revision (type: publish)
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs (CRITICAL)
    ├── WarmPageCacheJob
    ├── UpdateSitemapJob
    ├── InvalidateCDNJob
    ├── UpdateNavigationJob
    └── NotifySubscribersJob

[9] Dispatch PagePublished event
```

### 4. Implementation Notes
- الصفحات الرئيسية يُعاد تسخين cache فوراً
- تحديث sitemap.xml
- إبطال CDN cache للمسار

---

## 📌 العملية 9: جدولة النشر (Schedule)

*نفس نمط الخدمات*

---

## 📌 العملية 10: إلغاء النشر (Unpublish)

### 1. اسم العملية
`page.unpublish`

### 2. الشروط الخاصة
- لا يمكن إلغاء نشر الصفحة الرئيسية
- التحقق من الروابط الداخلية
- التحقق من القوائم

### 3. خطوات التنفيذ

```
[1] Check if critical page
    └── Block if homepage or required system page

[2] Find internal links to this page
    └── Warn user about affected content

[3] BEGIN TRANSACTION ─────────────────────────────────
    │
    [4] Update status → unpublished
    │
    [5] Remove from navigation (optional)
    │
    [6] Create placeholder redirect (410 or custom)
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── UpdateSitemapJob
    ├── InvalidateCDNJob
    └── NotifyLinkedContentOwnersJob
```

---

## 📌 العملية 11: الأرشفة (Archive)

*نفس نمط الخدمات*

---

## 📌 العملية 12: الاسترجاع (Restore)

*نفس نمط الخدمات*

---

## 📌 العملية 13: الحذف المؤقت (Soft Delete)

### 1. اسم العملية
`page.soft_delete`

### 2. الشروط الخاصة
- لا يمكن حذف صفحات النظام
- التعامل مع الصفحات الفرعية

### 3. خطوات التنفيذ

```
[1] Check if system page
    └── Block deletion of required pages

[2] Check for children
    └── Option: delete children, move children, block

[3] BEGIN TRANSACTION ─────────────────────────────────
    │
    [4] Handle children based on option
    │
    [5] Remove from all menus
    │
    [6] Update status → soft_deleted
    │
    [7] Set deleted_at
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs
    ├── UpdateAllMenusJob
    ├── UpdateSitemapJob
    └── CreateRedirectsJob
```

---

## 📌 العملية 14: الحذف النهائي (Permanent Delete)

*نفس نمط الخدمات مع:*
- حذف جميع الصفحات الفرعية
- حذف الـ redirects المرتبطة
- تسجيل المسار المحذوف لمنع إعادة الإنشاء خطأً

---

## 📌 العملية 15: إدارة النسخ (Revisions)

*نفس نمط الخدمات*

---

## 📌 العملية 16: الترجمة (Translation)

*نفس نمط الخدمات مع:*
- مزامنة الأقسام القابلة للترجمة
- الحفاظ على نفس القالب
- الحفاظ على الهيكل الهرمي

---

## 📌 العملية 17: ربط/فصل الوسائط

*نفس نمط الخدمات*

---

## 📌 العمليات الخاصة بالصفحات

### 18. إدارة الأقسام (Manage Sections)

#### 18.1 إضافة قسم (Add Section)
```http
POST /api/v1/pages/{id}/sections
{
  "type": "testimonials",
  "position": 3,
  "data": {...}
}
```

#### 18.2 إعادة ترتيب الأقسام (Reorder Sections)
```http
PUT /api/v1/pages/{id}/sections/reorder
{
  "sections": ["section-uuid-1", "section-uuid-2", ...]
}
```

#### 18.3 حذف قسم (Remove Section)
```http
DELETE /api/v1/pages/{id}/sections/{section_id}
```

### 19. إدارة التسلسل الهرمي (Hierarchy Management)

#### 19.1 نقل صفحة (Move Page)
```http
PUT /api/v1/pages/{id}/move
{
  "parent_id": "new-parent-uuid",
  "position": 2
}
```

**خطوات التنفيذ:**
```
[1] Validate no circular reference

[2] BEGIN TRANSACTION ─────────────────────────────────
    │
    [3] Update parent_id
    │
    [4] Recalculate order for siblings
    │
    [5] Update full_path for page and all descendants
    │   └── Recursive update
    │
    [6] Create redirects for old paths
    │
COMMIT ───────────────────────────────────────────────

[7] Queue UpdateNavigationJob
```

#### 19.2 استنساخ صفحة (Clone Page)
```http
POST /api/v1/pages/{id}/clone
{
  "new_slug": "about-us-copy",
  "include_children": false
}
```

### 20. معاينة الصفحة (Preview Page)

```http
GET /api/v1/pages/{id}/preview?token={preview_token}
```

**خطوات التنفيذ:**
```
[1] Generate short-lived preview token (15 min)

[2] Return preview URL with token

[3] Preview endpoint:
    ├── Validate token
    ├── Load draft_content
    └── Render with preview banner
```

### 21. تبديل القالب (Switch Template)

```http
PUT /api/v1/pages/{id}/template
{
  "template": "landing-page",
  "section_mapping": {
    "old_section_type": "new_section_type"
  }
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                      Page Lifecycle Flow                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Editor                    System                    Admin       │
│    │                         │                          │        │
│    │──── Create Page ───────▶│                          │        │
│    │     (with template)     │                          │        │
│    │◀─── Return Draft ───────│                          │        │
│    │                         │                          │        │
│    │──── Add Sections ──────▶│                          │        │
│    │                         │                          │        │
│    │──── Save Draft ────────▶│                          │        │
│    │     (auto-save)         │                          │        │
│    │                         │                          │        │
│    │──── Preview ───────────▶│                          │        │
│    │◀─── Preview URL ────────│                          │        │
│    │                         │                          │        │
│    │──── Submit Review ─────▶│                          │        │
│    │                         │──── Request Review ─────▶│        │
│    │                         │                          │        │
│    │                         │◀─── Approve ─────────────│        │
│    │                         │                          │        │
│    │──── Publish ───────────▶│                          │        │
│    │                         │──── Update Sitemap ──────│        │
│    │                         │──── Update Navigation ───│        │
│    │                         │──── Invalidate CDN ──────│        │
│    │                         │                          │        │
│    │──── Move Page ─────────▶│                          │        │
│    │     (change parent)     │──── Update Children ─────│        │
│    │                         │──── Create Redirects ────│        │
│    │                         │                          │        │
│    │──── Clone Page ────────▶│                          │        │
│    │◀─── New Draft ──────────│                          │        │
│    │                         │                          │        │
│    │──── Soft Delete ───────▶│                          │        │
│    │                         │──── Handle Children ─────│        │
│    │                         │──── Update Menus ────────│        │
│    │                         │                          │        │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الصفحات**
