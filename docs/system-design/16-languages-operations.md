# تحليل عمليات اللغات والترجمة (Languages & Localization Operations)

## 📋 نظرة عامة
نظام اللغات يدير التعدد اللغوي في المحتوى والواجهة. يدعم الترجمة الديناميكية، ملفات الترجمة، وآليات الـ fallback.

---

## 🔄 State Machine Diagram

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│ inactive │
└──────────┘            └──────────┘
                              │
                              ▼
                        ┌───────────┐
                        │  active   │
                        └───────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌──────────────┐
       │ inactive  │   │  default  │   │ soft_deleted │
       └───────────┘   └───────────┘   └──────────────┘
```

---

## 📌 العملية 1: إضافة لغة جديدة (Add New Language)

### 1. اسم العملية
`language.create`

### 2. الهدف
إضافة لغة جديدة للنظام مع إعداداتها الأساسية.

### 3. الشروط المسبقة
- صلاحية `language.create`
- رمز اللغة (ISO 639-1) صالح
- اللغة غير موجودة مسبقاً

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate ISO 639-1 code
    ├── Validate code uniqueness
    ├── Validate direction (ltr/rtl)
    └── Validate locale format

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Create Language Record
    │   └── INSERT INTO languages (id, code, locale, name, native_name, direction, script, status, ...)
    │
    [5] Create Default Translation Files
    │   ├── Copy from default language
    │   └── Mark all keys as untranslated
    │
    [6] Create Language Settings
    │   └── INSERT INTO language_settings (language_id, date_format, number_format, ...)
    │
    [7] Initialize Translation Progress
    │   └── INSERT INTO translation_progress (language_id, total_keys, translated_keys, ...)
    │
COMMIT TRANSACTION ───────────────────────────────────

[8] Dispatch Events
    └── LanguageCreated event

[9] Queue Jobs
    ├── GenerateTranslationFilesJob
    ├── CopyDefaultContentJob (optional)
    └── InvalidateLocaleCacheJob
```

### 5. الآثار الجانبية
- إنشاء سجل اللغة
- إنشاء ملفات الترجمة الأساسية
- تحديث قائمة اللغات المتاحة

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Invalid ISO Code | Return 422 + valid codes |
| Duplicate Language | Return 409 |
| File Creation Failed | Rollback, return 500 |

### 7. Security Considerations
- صلاحية خاصة لإدارة اللغات
- التحقق من صحة رمز ISO
- منع إنشاء لغات وهمية

### 8. Observability

```yaml
metrics:
  - language.create.count
  - language.active.count
  - translation.coverage.percentage

logs:
  fields:
    - code: {iso_code}
    - direction: {rtl/ltr}
    - created_by: {user_id}
```

### 9. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Localization Manager | ✅ |
| Others | ❌ |

### 10. API Endpoint

```http
POST /api/v1/languages
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "fr",
  "locale": "fr_FR",
  "name": "French",
  "native_name": "Français",
  "direction": "ltr",
  "script": "Latn",
  "flag_icon": "🇫🇷",
  "settings": {
    "date_format": "DD/MM/YYYY",
    "time_format": "HH:mm",
    "number_format": {
      "decimal_separator": ",",
      "thousands_separator": " "
    }
  },
  "copy_content_from": "en"
}
```

### 11. Webhook Payload

```json
{
  "event": "language.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "code": "fr",
    "locale": "fr_FR",
    "direction": "ltr",
    "status": "inactive"
  }
}
```

---

## 📌 العملية 2: تعديل إعدادات لغة (Update Language Settings)

### 1. اسم العملية
`language.update`

### 2. خطوات التنفيذ

```
[1] Load language

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update Language Record
    │
    [4] Update Language Settings
    │
    [5] If direction changed (rtl ↔ ltr):
    │   └── Queue RegenerateStylesJob
    │
COMMIT ───────────────────────────────────────────────

[6] Queue InvalidateLocaleCacheJob
```

### 3. API Endpoint

```http
PUT /api/v1/languages/{id}
{
  "name": "French (France)",
  "settings": {
    "date_format": "DD-MM-YYYY"
  }
}
```

---

## 📌 العملية 3: حذف لغة (Delete Language)

### 1. اسم العملية
`language.delete`

### 2. الشروط المسبقة
- ليست اللغة الافتراضية
- صلاحية `language.delete`

### 3. خطوات التنفيذ

```
[1] Check not default language

[2] Count affected content
    └── Get count of content with translations in this language

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Handle content translations
    │   ├── Option: delete translations
    │   └── Option: keep as orphaned
    │
    [5] Delete translation files
    │
    [6] Delete language settings
    │
    [7] Soft delete language
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs
    ├── CleanupTranslationsJob
    ├── InvalidateAllCachesJob
    └── UpdateSitemapJob
```

### 4. API Endpoint

```http
DELETE /api/v1/languages/{id}
{
  "handle_content": "delete",
  "confirm": true
}
```

---

## 📌 العملية 4: تفعيل/تعطيل لغة (Enable/Disable Language)

### 1. اسم العملية
`language.toggle_status`

### 2. خطوات التنفيذ

```
[1] Check not disabling default

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status
    │
    [4] If disabling:
    │   └── Remove from public language switcher
    │
    [5] If enabling:
    │   └── Check translation coverage threshold
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── InvalidateLocaleCacheJob
    ├── UpdateSitemapJob
    └── RegenerateLanguageSwitcherJob
```

### 3. API Endpoint

```http
POST /api/v1/languages/{id}/enable
POST /api/v1/languages/{id}/disable
```

---

## 📌 العملية 5: ضبط لغة افتراضية (Set Default Language)

### 1. اسم العملية
`language.set_default`

### 2. الهدف
تعيين لغة كافتراضية للنظام والمحتوى.

### 3. خطوات التنفيذ

```
[1] Validate language is active

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Remove default from current
    │   └── UPDATE languages SET is_default = false
    │
    [4] Set new default
    │   └── UPDATE languages SET is_default = true WHERE id = ?
    │
    [5] Update system settings
    │   └── UPDATE settings SET default_locale = ?
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs (CRITICAL)
    ├── UpdateFallbackConfigJob
    ├── InvalidateAllCachesJob
    ├── UpdateSitemapJob
    └── RegenerateRoutesJob
```

### 4. Implementation Notes
- اللغة الافتراضية تُستخدم كـ fallback
- تُستخدم عندما لا تتوفر ترجمة
- تُستخدم للزوار الجدد

### 5. API Endpoint

```http
POST /api/v1/languages/{id}/set-default
```

---

## 📌 العملية 6: مزامنة ملفات الترجمة (Sync Translations)

### 1. اسم العملية
`translation.sync`

### 2. الهدف
مزامنة ملفات الترجمة بين مصادر مختلفة.

### 3. خطوات التنفيذ

```
[1] Scan source files for translation keys
    └── Parse lang/*.json or lang/*.php files

[2] Compare with database
    ├── Find new keys (in files, not in DB)
    ├── Find removed keys (in DB, not in files)
    └── Find changed keys

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Add new keys to all languages
    │   └── Mark as untranslated
    │
    [5] Handle removed keys
    │   └── Mark as deprecated or delete
    │
    [6] Update changed values (for default language)
    │
    [7] Update translation progress stats
    │
COMMIT ───────────────────────────────────────────────

[8] Generate sync report
    └── Keys added: N, removed: N, changed: N

[9] Dispatch TranslationsSynced event
```

### 4. API Endpoint

```http
POST /api/v1/translations/sync
{
  "source": "files",
  "direction": "files_to_db",
  "dry_run": false
}
```

**Response:**
```json
{
  "success": true,
  "stats": {
    "keys_added": 15,
    "keys_removed": 3,
    "keys_updated": 8
  },
  "languages_affected": ["ar", "fr", "de"]
}
```

---

## 📌 العملية 7: استيراد حزم الترجمة (Import Translation Packs)

### 1. اسم العملية
`translation.import`

### 2. الهدف
استيراد ترجمات من ملفات خارجية.

### 3. الصيغ المدعومة
- JSON
- XLIFF
- PO/POT (gettext)
- CSV
- Excel

### 4. خطوات التنفيذ

```
[1] Validate file format

[2] Parse file content
    └── Extract key-value pairs

[3] Validate keys exist in system

[4] BEGIN TRANSACTION ────────────────────────────────
    │
    [5] For each translation:
    │   │
    │   [6] Find or create key
    │   │
    │   [7] Update translation value
    │   │
    │   [8] Mark as translated
    │
    [9] Update translation progress
    │
COMMIT ───────────────────────────────────────────────

[10] Queue Jobs
     ├── InvalidateTranslationCacheJob
     └── RegenerateTranslationFilesJob

[11] Generate import report
```

### 5. API Endpoint

```http
POST /api/v1/translations/import
Content-Type: multipart/form-data

file: translations.json
language: fr
mode: merge
```

---

## 📌 العملية 8: تصدير ملفات الترجمة (Export Translations)

### 1. اسم العملية
`translation.export`

### 2. خطوات التنفيذ

```
[1] Build export query
    ├── Filter by language(s)
    ├── Filter by group/namespace
    └── Filter by status (translated/untranslated)

[2] Fetch translations

[3] Transform to target format

[4] Generate file

[5] Return download URL
```

### 3. API Endpoint

```http
POST /api/v1/translations/export
{
  "languages": ["ar", "fr"],
  "groups": ["frontend", "emails"],
  "format": "json",
  "include_untranslated": true
}
```

---

## 📌 العملية 9: إنشاء نسخة مترجمة من محتوى (Create Translation Version)

### 1. اسم العملية
`content.create_translation`

### 2. الهدف
إنشاء نسخة مترجمة من محتوى موجود.

### 3. خطوات التنفيذ

```
[1] Load source content

[2] Check target language doesn't have translation

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Create translation record
    │   └── INSERT INTO content_translations (content_id, locale, ...)
    │
    [5] Copy non-translatable fields
    │   └── Copy media, relations, settings
    │
    [6] If copy_content:
    │   └── Copy content with [TRANSLATE] markers
    │
    [7] Set translation status → draft
    │
    [8] Create revision
    │
COMMIT ───────────────────────────────────────────────

[9] Dispatch ContentTranslationCreated event

[10] Queue Jobs
     └── NotifyTranslatorsJob
```

### 4. API Endpoint

```http
POST /api/v1/{content_type}/{id}/translations
{
  "locale": "fr",
  "copy_from": "en",
  "copy_content": true,
  "assign_to": "translator-user-uuid"
}
```

---

## 📌 العملية 10: مراجعة ترجمة (Review Translation)

### 1. اسم العملية
`translation.review`

### 2. خطوات التنفيذ

```
[1] Load translation

[2] Compare with source content

[3] Reviewer actions:
    ├── Edit translation
    ├── Add comments
    ├── Request changes
    ├── Approve
    └── Reject

[4] If approved:
    │
    [5] BEGIN TRANSACTION
    │   │
    │   [6] Update translation status → approved
    │   │
    │   [7] Set reviewed_by, reviewed_at
    │   │
    COMMIT
    │
    [8] Queue NotifyTranslatorJob

[9] Dispatch TranslationReviewed event
```

---

## 📌 العملية 11: نشر ترجمة (Publish Translation)

### 1. اسم العملية
`translation.publish`

### 2. خطوات التنفيذ

```
[1] Validate translation approved

[2] Validate required fields complete

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update translation status → published
    │
    [5] Set published_at
    │
    [6] Create revision (type: publish)
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── InvalidateContentCacheJob
    ├── UpdateSitemapJob
    └── IndexSearchJob (for this locale)
```

---

## 📌 العملية 12: فحص عدم اكتمال الترجمات (Missing Keys Detection)

### 1. اسم العملية
`translation.check_missing`

### 2. خطوات التنفيذ

```
[1] Get all translation keys from default language

[2] For each target language:
    │
    [3] Find missing keys
    │   └── Keys in default but not in target
    │
    [4] Find empty translations
    │   └── Keys with null or empty values
    │
    [5] Calculate coverage percentage
    │   └── (translated / total) * 100

[6] Generate report

[7] If coverage < threshold:
    └── Send alert
```

### 3. API Endpoint

```http
GET /api/v1/translations/missing?language=fr&group=frontend
```

**Response:**
```json
{
  "language": "fr",
  "total_keys": 500,
  "translated_keys": 450,
  "missing_keys": 50,
  "coverage_percentage": 90,
  "missing": [
    { "key": "messages.welcome", "group": "frontend" },
    { "key": "errors.validation.required", "group": "validation" }
  ]
}
```

---

## 📌 العملية 13: تحسين أداء نظام التعدد اللغوي (Localization Optimization)

### 1. اسم العملية
`localization.optimize`

### 2. استراتيجيات التحسين

```
[1] Translation Caching
    ├── Cache translations per locale
    ├── Cache duration: 24 hours
    └── Invalidate on translation update

[2] Lazy Loading
    ├── Load only needed translation groups
    └── Load on-demand for large sites

[3] Precompilation
    ├── Compile translations to optimized format
    └── Generate static files for production

[4] Database Optimization
    ├── Index by locale and key
    └── Partition large translation tables
```

### 3. API Endpoint

```http
POST /api/v1/translations/optimize
{
  "actions": ["cache", "compile", "cleanup"],
  "languages": ["all"]
}
```

---

## 📌 العملية 14: آليات Fallback عند غياب ترجمة

### 1. اسم العملية
`translation.fallback`

### 2. سلسلة الـ Fallback

```
[1] Try requested locale (e.g., "fr_CA")

[2] Try base language (e.g., "fr")

[3] Try default language (e.g., "en")

[4] Try fallback chain (configurable)
    └── e.g., fr → en → ar

[5] Return key name (development mode)

[6] Return empty or placeholder (production)
```

### 3. إعداد سلسلة الـ Fallback

```http
PUT /api/v1/languages/{id}/fallback
{
  "fallback_chain": ["en", "ar"]
}
```

### 4. تطبيق Fallback على المحتوى

```
[1] Request content in "fr"

[2] If translation exists and published:
    └── Return French version

[3] If translation exists but not published:
    └── Check show_unpublished setting

[4] If no translation:
    │
    [5] Check content fallback setting
    │   ├── "default": Show default language
    │   ├── "404": Return 404
    │   └── "redirect": Redirect to default
    │
    [6] Apply setting
```

---

## 📌 العملية 15: الترجمة التلقائية (Auto-Translation)

### 1. اسم العملية
`translation.auto_translate`

### 2. خطوات التنفيذ

```
[1] Get untranslated content/keys

[2] Call translation API
    ├── Google Translate
    ├── DeepL
    └── Microsoft Translator

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Store translations
    │
    [5] Mark as machine_translated
    │
    [6] Set status → needs_review
    │
COMMIT ───────────────────────────────────────────────

[7] Queue NotifyReviewersJob
```

### 3. API Endpoint

```http
POST /api/v1/translations/auto-translate
{
  "source_language": "en",
  "target_languages": ["ar", "fr"],
  "content_ids": ["uuid1", "uuid2"],
  "provider": "deepl",
  "auto_publish": false
}
```

---

## 📌 العملية 16: إدارة المترجمين (Translator Management)

### 1. تعيين مترجم

```http
POST /api/v1/translations/assign
{
  "user_id": "translator-uuid",
  "languages": ["ar", "fr"],
  "content_types": ["article", "page"]
}
```

### 2. تتبع أداء المترجم

```http
GET /api/v1/translators/{id}/stats
```

**Response:**
```json
{
  "translator_id": "uuid",
  "stats": {
    "total_translated": 500,
    "pending_review": 20,
    "approved": 450,
    "rejected": 30,
    "avg_approval_rate": 94,
    "languages": {
      "ar": { "translated": 300, "approved": 280 },
      "fr": { "translated": 200, "approved": 170 }
    }
  }
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                    Language Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin         System        Translator      Reviewer           │
│    │             │               │              │                │
│    │── Create ──▶│               │              │                │
│    │  Language   │               │              │                │
│    │             │               │              │                │
│    │── Enable ──▶│               │              │                │
│    │             │── Generate ───│              │                │
│    │             │   Files       │              │                │
│    │             │               │              │                │
│    │             │── Notify ────▶│              │                │
│    │             │               │              │                │
│    │             │◀── Translate ─│              │                │
│    │             │               │              │                │
│    │             │── Submit ─────────────────────▶│                │
│    │             │               │              │                │
│    │             │◀── Approve ────────────────────│                │
│    │             │               │              │                │
│    │── Publish ─▶│               │              │                │
│    │             │── Update ─────│              │                │
│    │             │   Sitemap     │              │                │
│    │             │               │              │                │
│    │             │── Check ──────│              │                │
│    │             │   Missing     │              │                │
│    │◀── Report ──│               │              │                │
│    │             │               │              │                │
│    │── Sync ────▶│               │              │                │
│    │             │── Compare ────│              │                │
│    │             │── Update ─────│              │                │
│    │             │               │              │                │
│    │── Set ─────▶│               │              │                │
│    │  Default    │── Update ─────│              │                │
│    │             │   Fallback    │              │                │
│    │             │               │              │                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📊 Translation Progress Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│                  Translation Coverage Overview                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Language    │ UI Strings │ Content  │ Overall │ Status         │
│  ────────────┼────────────┼──────────┼─────────┼────────────    │
│  English     │ 100%       │ 100%     │ 100%    │ ✅ Default     │
│  Arabic      │ 98%        │ 95%      │ 96%     │ ✅ Active      │
│  French      │ 85%        │ 60%      │ 72%     │ ⚠️ Active      │
│  German      │ 45%        │ 20%      │ 32%     │ 🔴 Inactive    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات اللغات والترجمة**
