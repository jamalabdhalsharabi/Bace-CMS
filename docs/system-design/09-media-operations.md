# تحليل عمليات الوسائط (Media Operations)

## 📋 نظرة عامة
الوسائط تشمل الصور، الفيديوهات، المستندات، والملفات الصوتية. تدعم التحويل التلقائي، التحسين، وإدارة المجلدات.

---

## 🔄 State Machine Diagram

```
┌──────────┐  upload    ┌────────────┐
│  (new)   │───────────▶│ processing │
└──────────┘            └────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌───────────┐
       │   ready   │   │  failed   │   │ quarantine│
       └───────────┘   └───────────┘   └───────────┘
              │                               │
              ▼                               ▼
       ┌───────────┐                   ┌───────────┐
       │   active  │                   │  deleted  │
       └───────────┘                   └───────────┘
              │
       ┌──────┴──────┐
       ▼             ▼
┌───────────┐ ┌──────────────┐
│ archived  │ │ soft_deleted │
└───────────┘ └──────────────┘
```

---

## 📌 العملية 1: رفع ملف (Upload Media)

### 1. اسم العملية
`media.upload`

### 2. الهدف
رفع ملف وسائط جديد مع معالجة وتحسين تلقائي.

### 3. الشروط المسبقة
- صلاحية `media.upload`
- نوع الملف مسموح
- الحجم ضمن الحد المسموح
- مساحة التخزين كافية

### 4. خطوات التنفيذ

```
[1] Validate Upload
    ├── Check file size limit
    ├── Check MIME type allowed
    ├── Validate file extension
    └── Virus/malware scan

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID and unique filename
    │
    [4] Create Media Record (status: processing)
    │   └── INSERT INTO media (id, filename, mime_type, size, status, ...)
    │
    [5] Store original file
    │   └── Save to temporary location
    │
COMMIT TRANSACTION ───────────────────────────────────

[6] Queue ProcessMediaJob (ASYNC)
    │
    [7] Job Execution:
        ├── Move to permanent storage
        ├── Extract metadata (EXIF, dimensions, duration)
        ├── Generate hash (for deduplication)
        ├── For Images:
        │   ├── Generate thumbnails (multiple sizes)
        │   ├── Optimize (compress, strip metadata)
        │   ├── Convert to WebP (optional)
        │   └── Generate blur placeholder
        ├── For Videos:
        │   ├── Generate poster/thumbnail
        │   ├── Extract duration
        │   ├── Transcode to web formats (optional)
        │   └── Generate preview clips
        ├── For Documents:
        │   ├── Extract text (OCR if image)
        │   ├── Generate preview image
        │   └── Extract page count
        └── For Audio:
            ├── Extract waveform
            └── Extract duration

[8] Update Media Record
    └── UPDATE status → ready, metadata, variants

[9] Dispatch MediaProcessed event
```

### 5. الآثار الجانبية
- تخزين الملف الأصلي
- توليد المتغيرات (thumbnails)
- استخراج البيانات الوصفية
- تحديث استخدام التخزين

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| File Too Large | Return 413 |
| Invalid Type | Return 415 |
| Virus Detected | Return 422 + quarantine |
| Processing Failed | Mark failed, allow retry |
| Storage Full | Return 507 |

### 7. Security Considerations
- فحص الفيروسات إجباري
- التحقق من MIME type الحقيقي (magic bytes)
- إعادة تسمية الملفات
- تخزين خارج web root
- signed URLs للوصول

### 8. Observability

```yaml
metrics:
  - media.upload.count
  - media.upload.size_bytes
  - media.upload.duration_ms
  - media.processing.duration_ms
  - media.storage.usage_bytes

logs:
  fields:
    - original_filename
    - mime_type
    - size_bytes
    - processing_time_ms
```

### 9. API Endpoint

```http
POST /api/v1/media
Content-Type: multipart/form-data

file: [binary]
folder_id: uuid (optional)
alt_text: { "ar": "...", "en": "..." }
title: { "ar": "...", "en": "..." }
```

**Response:**
```json
{
  "data": {
    "id": "uuid",
    "filename": "image_abc123.jpg",
    "status": "processing",
    "upload_url": null
  }
}
```

### Chunked Upload (Large Files):
```http
POST /api/v1/media/init-upload
{
  "filename": "video.mp4",
  "size": 524288000,
  "mime_type": "video/mp4"
}

Response:
{
  "upload_id": "uuid",
  "chunk_size": 5242880,
  "upload_urls": [...]
}

PUT /api/v1/media/{upload_id}/chunk/{index}
[binary chunk]

POST /api/v1/media/{upload_id}/complete
```

### 10. Webhook Payload

```json
{
  "event": "media.processed",
  "payload": {
    "id": "uuid",
    "type": "image",
    "variants": {
      "thumbnail": { "url": "...", "width": 150, "height": 150 },
      "medium": { "url": "...", "width": 600, "height": 400 },
      "large": { "url": "...", "width": 1200, "height": 800 }
    },
    "metadata": {
      "width": 3000,
      "height": 2000,
      "format": "jpeg"
    }
  }
}
```

---

## 📌 العملية 2: تعديل بيانات الوسائط (Update Media)

### 1. اسم العملية
`media.update`

### 2. خطوات التنفيذ

```
[1] Validate request

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update Media Record
    │   └── UPDATE title, alt_text, description, folder_id
    │
    [4] Update translations
    │
COMMIT ───────────────────────────────────────────────

[5] Queue InvalidateCDNCacheJob (if URL changed)
```

---

## 📌 العملية 3: استبدال الملف (Replace File)

```http
POST /api/v1/media/{id}/replace
Content-Type: multipart/form-data

file: [binary]
keep_old: false
```

**خطوات التنفيذ:**
```
[1] Upload new file

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Archive old file (if keep_old)
    │
    [4] Update media record with new file
    │
    [5] Preserve original metadata/translations
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── ProcessMediaJob
    ├── InvalidateCDNCacheJob
    └── UpdateReferencesJob
```

---

## 📌 العملية 4: نقل إلى مجلد (Move to Folder)

```http
PUT /api/v1/media/{id}/move
{
  "folder_id": "uuid"
}
```

---

## 📌 العملية 5: نسخ (Duplicate)

```http
POST /api/v1/media/{id}/duplicate
{
  "folder_id": "uuid",
  "copy_metadata": true
}
```

**خطوات التنفيذ:**
```
[1] Copy physical file

[2] Create new media record

[3] Copy translations and metadata

[4] Generate new variants
```

---

## 📌 العملية 6: تحسين الصورة (Optimize Image)

```http
POST /api/v1/media/{id}/optimize
{
  "quality": 85,
  "strip_metadata": true,
  "convert_to": "webp"
}
```

**خطوات التنفيذ:**
```
[1] Queue OptimizeImageJob

[2] Job execution:
    ├── Load original
    ├── Apply optimizations
    ├── Save optimized version
    └── Regenerate variants

[3] Update size in record
```

---

## 📌 العملية 7: قص/تعديل الصورة (Crop/Edit Image)

```http
POST /api/v1/media/{id}/crop
{
  "x": 100,
  "y": 50,
  "width": 800,
  "height": 600,
  "save_as_new": false
}
```

**خطوات التنفيذ:**
```
[1] Load original image

[2] Apply crop

[3] If save_as_new:
    └── Create new media record
    
[4] Else:
    ├── Archive original
    └── Replace with cropped

[5] Regenerate all variants
```

---

## 📌 العملية 8: إنشاء مجلد (Create Folder)

```http
POST /api/v1/media/folders
{
  "name": "صور المنتجات",
  "parent_id": "uuid"
}
```

---

## 📌 العملية 9: البحث في الوسائط (Search Media)

```http
GET /api/v1/media?search=logo&type=image&folder=uuid
```

**معايير البحث:**
- اسم الملف
- Alt text
- العنوان
- نوع الملف
- التاريخ
- الحجم
- المجلد
- الأبعاد

---

## 📌 العملية 10: الحذف المؤقت (Soft Delete)

```http
DELETE /api/v1/media/{id}
```

**خطوات التنفيذ:**
```
[1] Check references
    └── Warn if used by content

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → soft_deleted
    │
    [4] Set deleted_at
    │
COMMIT ───────────────────────────────────────────────

[5] Queue SchedulePermanentDeleteJob (30 days)
```

---

## 📌 العملية 11: الحذف النهائي (Permanent Delete)

```http
DELETE /api/v1/media/{id}?permanent=true
```

**خطوات التنفيذ:**
```
[1] Check no active references

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Delete all variants from storage
    │
    [4] Delete original file
    │
    [5] Delete database record
    │
    [6] Update storage usage
    │
COMMIT ───────────────────────────────────────────────

[7] Queue InvalidateCDNCacheJob
```

---

## 📌 العملية 12: استرجاع (Restore)

```http
POST /api/v1/media/{id}/restore
```

---

## 📌 العملية 13: توليد رابط مؤقت (Generate Signed URL)

```http
POST /api/v1/media/{id}/signed-url
{
  "expires_in": 3600,
  "variant": "original"
}
```

**Response:**
```json
{
  "url": "https://cdn.example.com/media/xxx?signature=xxx&expires=xxx",
  "expires_at": "2024-01-15T11:00:00Z"
}
```

---

## 📌 العملية 14: تحليل الاستخدام (Usage Analysis)

```http
GET /api/v1/media/{id}/usage
```

**Response:**
```json
{
  "used_in": [
    { "type": "article", "id": "uuid", "title": "..." },
    { "type": "product", "id": "uuid", "title": "..." }
  ],
  "total_references": 5
}
```

---

## 📌 العملية 15: حذف جماعي (Bulk Delete)

```http
POST /api/v1/media/bulk-delete
{
  "ids": ["uuid1", "uuid2"],
  "permanent": false
}
```

---

## 📌 العملية 16: نقل جماعي (Bulk Move)

```http
POST /api/v1/media/bulk-move
{
  "ids": ["uuid1", "uuid2"],
  "folder_id": "uuid"
}
```

---

## 📌 العملية 17: تنظيف غير المستخدمة (Cleanup Unused)

```http
POST /api/v1/media/cleanup
{
  "older_than_days": 90,
  "dry_run": true
}
```

**خطوات التنفيذ:**
```
[1] Find unused media
    └── Not referenced by any content
    └── Older than X days

[2] If dry_run:
    └── Return list without deleting

[3] Else:
    └── Queue BulkDeleteJob
```

---

## 📌 العملية 18: إزالة التكرار (Deduplicate)

```http
POST /api/v1/media/deduplicate
{
  "folder_id": "uuid",
  "dry_run": true
}
```

**خطوات التنفيذ:**
```
[1] Find files with same hash

[2] Group duplicates

[3] Keep oldest, mark others

[4] If not dry_run:
    └── Update references to point to kept file
    └── Delete duplicates
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                      Media Lifecycle Flow                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  User            System           Storage          CDN          │
│    │               │                │               │            │
│    │── Upload ────▶│                │               │            │
│    │               │── Scan ────────│               │            │
│    │               │── Store ──────▶│               │            │
│    │               │                │               │            │
│    │               │── Process ─────│               │            │
│    │               │   (thumbnails) │               │            │
│    │               │                │               │            │
│    │               │── Push ───────────────────────▶│            │
│    │◀── Ready ─────│                │               │            │
│    │               │                │               │            │
│    │── Use in ────▶│                │               │            │
│    │   content     │                │               │            │
│    │               │                │               │            │
│    │── Optimize ──▶│                │               │            │
│    │               │── Reprocess ───│               │            │
│    │               │── Invalidate ──────────────────▶│            │
│    │               │                │               │            │
│    │── Delete ────▶│                │               │            │
│    │               │── Remove ─────▶│               │            │
│    │               │── Purge ──────────────────────▶│            │
│    │               │                │               │            │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الوسائط**
