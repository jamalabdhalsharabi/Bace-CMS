# تحليل عمليات الأقسام الثابتة (Static Blocks/Sections Operations)

## 📋 نظرة عامة
الأقسام الثابتة هي مكونات محتوى قابلة لإعادة الاستخدام (مثل: بانرات، CTAs، footers). تدعم التضمين في صفحات متعددة والتعدد اللغوي.

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
       │unpublished│   │ archived  │   │ soft_deleted │
       └───────────┘   └───────────┘   └──────────────┘
```

---

## 📌 العملية 1: إنشاء قسم ثابت (Create Static Block)

### 1. اسم العملية
`static_block.create`

### 2. الهدف
إنشاء قسم محتوى قابل لإعادة الاستخدام.

### 3. الشروط المسبقة
- صلاحية `static_block.create`
- المعرف (identifier) فريد
- نوع القسم صالح

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate identifier uniqueness
    ├── Validate block type exists
    └── Validate content structure per type

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Create Block Record
    │   └── INSERT INTO static_blocks (id, identifier, type, status, ...)
    │
    [5] Create Translation Records
    │   └── INSERT INTO static_block_translations (block_id, locale, title, content, ...)
    │
    [6] Process Media
    │
    [7] Store Block Settings
    │   └── INSERT INTO static_block_settings (block_id, key, value)
    │
COMMIT TRANSACTION ───────────────────────────────────

[8] Dispatch StaticBlockCreated event

[9] Queue InvalidateBlockCacheJob
```

### 5. أنواع الأقسام

| النوع | الوصف |
|------|-------|
| `html` | محتوى HTML حر |
| `banner` | بانر مع صورة ونص وزر |
| `cta` | Call to Action |
| `hero` | قسم رئيسي |
| `testimonials_slider` | عرض توصيات |
| `contact_info` | معلومات الاتصال |
| `social_links` | روابط التواصل |
| `newsletter` | نموذج الاشتراك |
| `features` | قائمة الميزات |
| `stats` | إحصائيات |

### 6. API Endpoint

```http
POST /api/v1/static-blocks
{
  "identifier": "homepage-hero",
  "type": "hero",
  "translations": {
    "ar": {
      "title": "مرحباً بكم",
      "subtitle": "نقدم أفضل الخدمات",
      "cta_text": "تعرف علينا",
      "cta_url": "/about"
    }
  },
  "settings": {
    "background_type": "image",
    "background_image_id": "media-uuid",
    "overlay_opacity": 0.5,
    "text_alignment": "center"
  },
  "visibility": {
    "pages": ["homepage"],
    "show_on_mobile": true
  }
}
```

### 7. Webhook Payload

```json
{
  "event": "static_block.created",
  "payload": {
    "id": "uuid",
    "identifier": "homepage-hero",
    "type": "hero",
    "status": "draft"
  }
}
```

---

## 📌 العملية 2: تعديل قسم (Update Block)

```http
PUT /api/v1/static-blocks/{id}
```

**خطوات التنفيذ:**
```
[1] Load block

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Create Revision
    │
    [4] Update Block Record
    │
    [5] Sync Translations
    │
    [6] Update Settings
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── InvalidateBlockCacheJob
    └── InvalidatePagesCacheJob (all pages using this block)
```

---

## 📌 العملية 3: النشر (Publish)

```http
POST /api/v1/static-blocks/{id}/publish
```

**خطوات التنفيذ:**
```
[1] Validate content complete

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs (CRITICAL)
    ├── WarmBlockCacheJob
    └── InvalidatePagesCacheJob
```

---

## 📌 العملية 4: استنساخ (Clone Block)

```http
POST /api/v1/static-blocks/{id}/clone
{
  "new_identifier": "homepage-hero-v2"
}
```

---

## 📌 العملية 5: تضمين في صفحة (Embed in Page)

```http
POST /api/v1/pages/{page_id}/blocks
{
  "block_id": "static-block-uuid",
  "position": "before_content",
  "order": 1
}
```

---

## 📌 العملية 6: البحث عن الاستخدامات (Find Usages)

```http
GET /api/v1/static-blocks/{id}/usages
```

**Response:**
```json
{
  "usages": [
    { "type": "page", "id": "uuid", "title": "الصفحة الرئيسية" },
    { "type": "template", "id": "uuid", "name": "default" }
  ],
  "total": 5
}
```

---

## 📌 العملية 7: معاينة (Preview)

```http
GET /api/v1/static-blocks/{id}/preview?locale=ar
```

---

## 📌 العملية 8: تصدير/استيراد

```http
GET /api/v1/static-blocks/{id}/export

POST /api/v1/static-blocks/import
{
  "block": {...},
  "translations": {...}
}
```

---

## 📌 العملية 9: إدارة الرؤية (Visibility Management)

```http
PUT /api/v1/static-blocks/{id}/visibility
{
  "rules": [
    { "type": "page", "pages": ["homepage", "about"] },
    { "type": "device", "devices": ["desktop", "tablet"] },
    { "type": "user_role", "roles": ["guest"] },
    { "type": "date_range", "from": "...", "to": "..." }
  ]
}
```

---

## 📌 العملية 10: جدولة الظهور (Schedule Visibility)

```http
POST /api/v1/static-blocks/{id}/schedule
{
  "show_from": "2024-02-01T00:00:00Z",
  "show_until": "2024-02-14T23:59:59Z"
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                   Static Block Lifecycle Flow                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin           System           Cache           Page          │
│    │               │                │               │            │
│    │── Create ────▶│                │               │            │
│    │               │                │               │            │
│    │── Configure ─▶│                │               │            │
│    │   (settings)  │                │               │            │
│    │               │                │               │            │
│    │── Publish ───▶│                │               │            │
│    │               │── Cache ──────▶│               │            │
│    │               │                │               │            │
│    │── Embed ─────▶│                │               │            │
│    │   (in page)   │── Link ───────────────────────▶│            │
│    │               │                │               │            │
│    │               │                │       ◀── Get ─│            │
│    │               │                │── Serve ─────▶│            │
│    │               │                │               │            │
│    │── Update ────▶│                │               │            │
│    │               │── Invalidate ─▶│               │            │
│    │               │── Invalidate ─────────────────▶│            │
│    │               │   (pages)      │               │            │
│    │               │                │               │            │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الأقسام الثابتة**
