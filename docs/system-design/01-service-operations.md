# تحليل عمليات الخدمات (Service Operations)

## 📋 نظرة عامة
الخدمات هي كيانات المحتوى التي تصف الخدمات المقدمة من المؤسسة. تدعم التعدد اللغوي والوسائط المتعددة.

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
                                      │
                                      ▼
                              ┌──────────────┐
                              │ soft_deleted │
                              └──────────────┘
```

---

## 📌 العملية 1: إنشاء خدمة (Create Service)

### 1. اسم العملية
`service.create`

### 2. الهدف
إنشاء سجل خدمة جديد في حالة المسودة مع دعم التعدد اللغوي.

### 3. الشروط المسبقة
- المستخدم مصادق عليه
- المستخدم يملك صلاحية `service.create`
- البيانات المطلوبة صالحة
- اللغة الافتراضية متاحة

### 4. خطوات التنفيذ التفصيلية

```
[1] Validate Request
    ├── Sanitize input data
    ├── Validate required fields per language
    └── Validate media references exist

[2] Authorization Check
    └── Gate::authorize('service.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Service Record
    │   └── INSERT INTO services (id, author_id, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO service_translations (service_id, locale, ...)
    │
    [7] Create Initial Revision
    │   └── INSERT INTO service_revisions (service_id, revision_number, data, ...)
    │
    [8] Process Media Attachments
    │   └── INSERT INTO service_media (service_id, media_id, collection, order)
    │
    [9] Process Taxonomy Relations
    │   └── INSERT INTO service_categories (service_id, category_id)
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Dispatch Events (async)
     ├── ServiceCreated event
     └── ContentChanged event

[11] Queue Background Jobs
     ├── GenerateSlugJob (if auto-slug enabled)
     ├── IndexSearchJob
     └── InvalidateCacheJob

[12] Return Response
```

### 5. الآثار الجانبية
- إنشاء سجل في جدول الخدمات
- إنشاء سجلات الترجمة
- إنشاء أول revision
- إنشاء علاقات الوسائط
- تحديث فهرس البحث (async)
- إبطال cache ذات الصلة

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Validation Error | Return 422 + error details |
| Authorization Error | Return 403 |
| Duplicate Slug | Append unique suffix |
| Media Not Found | Return 422 + missing media IDs |
| DB Error | Rollback + Return 500 |
| Queue Failure | Log + Retry mechanism |

### 7. Idempotency & Concurrency
- **Idempotency**: استخدام `X-Idempotency-Key` header
- **Concurrency**: لا توجد مشاكل (إنشاء جديد)
- **Duplicate Prevention**: فحص الـ slug قبل الإنشاء

### 8. Security Considerations
- تنظيف HTML من XSS
- التحقق من صلاحية الوصول للوسائط
- Rate limiting: 30 request/minute
- Audit logging للعملية

### 9. Observability

```yaml
metrics:
  - service.create.count
  - service.create.duration_ms
  - service.create.errors

logs:
  level: info
  fields:
    - action: service.create
    - actor_id: {user_id}
    - service_id: {new_id}
    - duration_ms: {time}

alerts:
  - condition: error_rate > 5%
    severity: warning
```

### 10. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Editor | ✅ |
| Author | ✅ |
| Contributor | ✅ (draft only) |
| Reviewer | ❌ |
| Viewer | ❌ |

### 11. External Dependencies
- Database (required)
- Search Engine (optional, async)
- Cache System (optional)
- Media Storage (if attaching media)

### 12. API Endpoint Example

```http
POST /api/v1/services
Authorization: Bearer {token}
Content-Type: application/json
X-Idempotency-Key: {uuid}

{
  "translations": {
    "ar": { "title": "...", "description": "..." },
    "en": { "title": "...", "description": "..." }
  },
  "categories": ["uuid1", "uuid2"],
  "media": [
    { "id": "uuid", "collection": "featured" }
  ],
  "status": "draft"
}
```

**Response 201:**
```json
{
  "data": {
    "id": "uuid",
    "status": "draft",
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

### 13. Webhook Payload Example

```json
{
  "event": "service.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "type": "service",
    "action": "create",
    "actor": { "id": "user_uuid", "type": "user" },
    "data": {
      "status": "draft",
      "locales": ["ar", "en"]
    }
  }
}
```

### 14. Implementation Notes
- استخدام UUID v7 للترتيب الزمني
- إنشاء slug تلقائي من العنوان إذا لم يُحدد
- الـ revision الأول يأخذ الرقم 1
- التخزين المؤقت يُبطل عند الإنشاء

---

## 📌 العملية 2: تعديل خدمة (Update Service)

### 1. اسم العملية
`service.update`

### 2. الهدف
تحديث بيانات خدمة موجودة مع الحفاظ على سجل التغييرات.

### 3. الشروط المسبقة
- الخدمة موجودة وليست محذوفة
- المستخدم مصرح له (مالك أو لديه صلاحية)
- الحالة تسمح بالتعديل (`draft`, `rejected`, `unpublished`)
- البيانات صالحة

### 4. خطوات التنفيذ

```
[1] Validate & Authorize
    ├── Validate input
    ├── Load service with lock
    └── Check can_update policy

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Acquire Row Lock
    │   └── SELECT ... FOR UPDATE
    │
    [4] Check Version (Optimistic Locking)
    │   └── Compare version number
    │
    [5] Create New Revision ──────────────────────────
    │   └── INSERT INTO service_revisions
    │
    [6] Update Service Record
    │   └── UPDATE services SET ... , version = version + 1
    │
    [7] Sync Translations
    │   ├── UPDATE existing
    │   ├── INSERT new locales
    │   └── DELETE removed locales
    │
    [8] Sync Media Relations
    │   └── SYNC service_media
    │
    [9] Sync Taxonomy Relations
    │   └── SYNC service_categories
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Dispatch Events
     └── ServiceUpdated event

[11] Queue Jobs
     ├── ReindexSearchJob
     ├── InvalidateCacheJob
     └── NotifySubscribersJob (if published)
```

### 5. الآثار الجانبية
- تحديث سجل الخدمة
- إنشاء revision جديد
- تحديث الترجمات
- تحديث فهرس البحث
- إبطال الـ cache

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Not Found | Return 404 |
| Conflict (version mismatch) | Return 409 + current version |
| Invalid State | Return 422 + allowed states |
| Lock Timeout | Return 423 + retry-after |

### 7. Idempotency & Concurrency
- **Optimistic Locking**: حقل `version` للكشف عن التعارضات
- **Pessimistic Locking**: `SELECT FOR UPDATE` خلال المعاملة
- **Retry Strategy**: 3 محاولات مع exponential backoff

### 8. Security Considerations
- التحقق من ملكية المحتوى
- منع تعديل المحتوى المنشور مباشرة (يتطلب unpublish أولاً)
- تسجيل كل التغييرات في audit log

### 9. Observability

```yaml
metrics:
  - service.update.count
  - service.update.conflicts
  - service.update.duration_ms

logs:
  fields:
    - changed_fields: [...]
    - previous_version: N
    - new_version: N+1
```

### 10. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ أي خدمة |
| Admin | ✅ أي خدمة |
| Editor | ✅ أي خدمة |
| Author | ✅ خدماته فقط |
| Contributor | ✅ مسوداته فقط |

### 11. API Endpoint

```http
PUT /api/v1/services/{id}
If-Match: "{version}"

{ ... updated data ... }
```

### 12. Webhook Payload

```json
{
  "event": "service.updated",
  "payload": {
    "id": "uuid",
    "changes": {
      "title": { "old": "...", "new": "..." }
    },
    "revision": 5
  }
}
```

---

## 📌 العملية 3: حفظ كمسودة (Save Draft)

### 1. اسم العملية
`service.save_draft`

### 2. الهدف
حفظ التغييرات الحالية دون تغيير حالة النشر، مع دعم الحفظ التلقائي.

### 3. الشروط المسبقة
- الخدمة في حالة قابلة للتحرير
- المستخدم مالك أو محرر

### 4. خطوات التنفيذ

```
[1] Validate partial data (less strict)

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update service (no status change)
    │
    [4] Create Revision (type: auto_save)
    │
COMMIT ───────────────────────────────────────────────

[5] Dispatch ServiceDraftSaved event

[6] NO search reindex (draft not searchable publicly)
```

### 5. Implementation Notes
- دعم Auto-save كل 30 ثانية
- الحد الأقصى للـ auto-save revisions: 10 (يُحذف الأقدم)
- لا يُطلق webhooks خارجية

---

## 📌 العملية 4: إرسال للمراجعة (Submit for Review)

### 1. اسم العملية
`service.submit_for_review`

### 2. الهدف
نقل الخدمة من حالة المسودة إلى قائمة انتظار المراجعة.

### 3. الشروط المسبقة
- الحالة الحالية: `draft` أو `rejected`
- البيانات المطلوبة مكتملة
- المستخدم مالك المحتوى

### 4. خطوات التنفيذ

```
[1] Validate completeness
    ├── Required fields filled
    ├── At least one translation complete
    └── Featured image attached

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → pending_review
    │
    [4] Set submitted_at timestamp
    │
    [5] Create Revision (type: submission)
    │
COMMIT ───────────────────────────────────────────────

[6] Queue NotifyReviewersJob
    └── Send notifications to users with review permission

[7] Dispatch ServiceSubmittedForReview event
```

### 5. Roles & Permissions
- Author, Contributor: يمكنهم الإرسال
- يتم إشعار: Editors, Reviewers

### 6. API Endpoint

```http
POST /api/v1/services/{id}/submit-for-review
```

---

## 📌 العملية 5: المراجعة (Review)

### 1. اسم العملية
`service.start_review`

### 2. الهدف
بدء عملية مراجعة الخدمة وتعيين المراجع.

### 3. الشروط المسبقة
- الحالة: `pending_review`
- المستخدم لديه صلاحية `service.review`
- الخدمة غير مقفلة من مراجع آخر

### 4. خطوات التنفيذ

```
[1] Acquire review lock
    └── Prevent other reviewers

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → in_review
    │
    [4] Set reviewer_id, review_started_at
    │
COMMIT ───────────────────────────────────────────────

[5] Notify author (review started)

[6] Set review lock expiry (30 minutes)
```

### 5. Implementation Notes
- القفل ينتهي تلقائياً بعد 30 دقيقة
- يمكن للمراجع تمديد القفل
- Super Admin يمكنه كسر القفل

---

## 📌 العملية 6: الموافقة (Approve)

### 1. اسم العملية
`service.approve`

### 2. الهدف
الموافقة على الخدمة بعد المراجعة.

### 3. الشروط المسبقة
- الحالة: `in_review`
- المستخدم هو المراجع الحالي أو Admin
- صلاحية `service.approve`

### 4. خطوات التنفيذ

```
[1] Validate reviewer is current

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → approved
    │
    [4] Set approved_at, approved_by
    │
    [5] Release review lock
    │
    [6] Create Revision (type: approval)
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── NotifyAuthorJob (approved)
    └── PrepareForPublishJob (optional auto-publish)

[8] Dispatch ServiceApproved event
```

### 5. API Endpoint

```http
POST /api/v1/services/{id}/approve
{
  "notes": "Optional approval notes",
  "auto_publish": false
}
```

---

## 📌 العملية 7: الرفض (Reject)

### 1. اسم العملية
`service.reject`

### 2. الهدف
رفض الخدمة مع توضيح السبب.

### 3. الشروط المسبقة
- الحالة: `in_review` أو `pending_review`
- صلاحية `service.reject`
- سبب الرفض مطلوب

### 4. خطوات التنفيذ

```
[1] Validate rejection reason provided

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → rejected
    │
    [4] Store rejection reason & reviewer
    │
    [5] Release review lock
    │
    [6] Create Revision (type: rejection)
    │
COMMIT ───────────────────────────────────────────────

[7] Queue NotifyAuthorJob
    └── Include rejection reason

[8] Dispatch ServiceRejected event
```

### 5. Webhook Payload

```json
{
  "event": "service.rejected",
  "payload": {
    "id": "uuid",
    "reason": "...",
    "rejected_by": "user_uuid"
  }
}
```

---

## 📌 العملية 8: النشر (Publish)

### 1. اسم العملية
`service.publish`

### 2. الهدف
نشر الخدمة وإتاحتها للعامة.

### 3. الشروط المسبقة
- الحالة: `approved` أو `scheduled` أو `unpublished`
- صلاحية `service.publish`
- جميع الترجمات المطلوبة مكتملة

### 4. خطوات التنفيذ

```
[1] Validate publishable state

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at (first time) or republished_at
    │
    [5] Set published_by
    │
    [6] Generate/update canonical URL
    │
    [7] Create Revision (type: publish)
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs (HIGH PRIORITY)
    ├── IndexSearchJob
    ├── InvalidateCDNCacheJob
    ├── GenerateSitemapJob
    ├── PingSearchEnginesJob
    └── NotifySubscribersJob

[9] Dispatch ServicePublished event

[10] Trigger external webhooks
```

### 5. الآثار الجانبية
- الخدمة متاحة للعامة
- تحديث sitemap
- إشعار محركات البحث
- إبطال CDN cache
- تحديث فهرس البحث

### 6. API Endpoint

```http
POST /api/v1/services/{id}/publish
```

---

## 📌 العملية 9: جدولة النشر (Schedule)

### 1. اسم العملية
`service.schedule`

### 2. الهدف
جدولة نشر الخدمة في وقت مستقبلي.

### 3. الشروط المسبقة
- الحالة: `approved`
- تاريخ النشر في المستقبل
- صلاحية `service.schedule`

### 4. خطوات التنفيذ

```
[1] Validate scheduled_at is future

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → scheduled
    │
    [4] Set scheduled_at, scheduled_by
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Delayed Job
    └── PublishServiceJob::dispatch()->delay($scheduled_at)

[6] Dispatch ServiceScheduled event
```

### 5. Background Processing
- Scheduler يفحص كل دقيقة
- الـ Job يتحقق من الحالة قبل النشر
- إذا تغيرت الحالة، يُلغى الـ Job

### 6. API Endpoint

```http
POST /api/v1/services/{id}/schedule
{
  "scheduled_at": "2024-02-01T09:00:00Z"
}
```

---

## 📌 العملية 10: إلغاء النشر (Unpublish)

### 1. اسم العملية
`service.unpublish`

### 2. الهدف
إزالة الخدمة من العرض العام مع الحفاظ على البيانات.

### 3. الشروط المسبقة
- الحالة: `published`
- صلاحية `service.unpublish`

### 4. خطوات التنفيذ

```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → unpublished
    │
    [3] Set unpublished_at, unpublished_by
    │
    [4] Store unpublish reason (optional)
    │
    [5] Create Revision (type: unpublish)
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── RemoveFromSearchIndexJob
    ├── InvalidateCDNCacheJob
    └── UpdateSitemapJob

[7] Dispatch ServiceUnpublished event
```

### 5. Implementation Notes
- الروابط القديمة تُرجع 410 Gone أو redirect
- يمكن إعادة النشر بدون مراجعة

---

## 📌 العملية 11: الأرشفة (Archive)

### 1. اسم العملية
`service.archive`

### 2. الهدف
نقل الخدمة للأرشيف للحفاظ عليها بدون عرضها.

### 3. الشروط المسبقة
- الحالة: `published` أو `unpublished`
- صلاحية `service.archive`

### 4. خطوات التنفيذ

```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → archived
    │
    [3] Set archived_at, archived_by
    │
    [4] Move media to archive storage (optional)
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── RemoveFromSearchIndexJob
    └── CompressMediaJob (optional)
```

---

## 📌 العملية 12: الاسترجاع (Restore)

### 1. اسم العملية
`service.restore`

### 2. الهدف
استرجاع خدمة من الأرشيف أو من الحذف المؤقت.

### 3. الشروط المسبقة
- الحالة: `archived` أو `soft_deleted`
- صلاحية `service.restore`

### 4. خطوات التنفيذ

```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → unpublished (default)
    │
    [3] Clear deleted_at if soft deleted
    │
    [4] Set restored_at, restored_by
    │
    [5] Create Revision (type: restore)
    │
COMMIT ───────────────────────────────────────────────

[6] Dispatch ServiceRestored event
```

---

## 📌 العملية 13: الحذف المؤقت (Soft Delete)

### 1. اسم العملية
`service.soft_delete`

### 2. الهدف
حذف الخدمة مع إمكانية الاسترجاع.

### 3. الشروط المسبقة
- صلاحية `service.delete`
- ليست محذوفة مسبقاً

### 4. خطوات التنفيذ

```
[1] Check for dependencies
    └── Warn if linked from other content

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → soft_deleted
    │
    [4] Set deleted_at, deleted_by
    │
    [5] Detach from navigation/menus
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── RemoveFromSearchIndexJob
    ├── InvalidateCacheJob
    └── SchedulePermanentDeleteJob (after retention period)
```

### 5. Implementation Notes
- فترة الاحتفاظ الافتراضية: 30 يوم
- بعدها يُحذف تلقائياً نهائياً

---

## 📌 العملية 14: الحذف النهائي (Permanent Delete)

### 1. اسم العملية
`service.force_delete`

### 2. الهدف
حذف الخدمة نهائياً من قاعدة البيانات.

### 3. الشروط المسبقة
- الحالة: `soft_deleted`
- صلاحية `service.force_delete`
- تأكيد صريح (confirmation token)

### 4. خطوات التنفيذ

```
[1] Validate confirmation token

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Delete all revisions
    │   └── DELETE FROM service_revisions WHERE service_id = ?
    │
    [4] Delete all translations
    │   └── DELETE FROM service_translations WHERE service_id = ?
    │
    [5] Detach all media (don't delete media files)
    │   └── DELETE FROM service_media WHERE service_id = ?
    │
    [6] Detach all taxonomies
    │   └── DELETE FROM service_categories WHERE service_id = ?
    │
    [7] Delete service record
    │   └── DELETE FROM services WHERE id = ?
    │
    [8] Log permanent deletion in audit
    │
COMMIT ───────────────────────────────────────────────

[9] Dispatch ServicePermanentlyDeleted event
```

### 5. Security Considerations
- يتطلب تأكيد بكلمة المرور أو 2FA
- غير قابل للتراجع
- يُسجل في audit log الدائم

---

## 📌 العملية 15: إدارة النسخ (Revisions Management)

### 1. اسم العملية
`service.manage_revisions`

### 2. العمليات الفرعية

#### 15.1 عرض النسخ (List Revisions)
```http
GET /api/v1/services/{id}/revisions
```

#### 15.2 عرض نسخة محددة (View Revision)
```http
GET /api/v1/services/{id}/revisions/{revision_number}
```

#### 15.3 مقارنة نسختين (Compare Revisions)
```http
GET /api/v1/services/{id}/revisions/compare?from=3&to=5
```

#### 15.4 استرجاع نسخة (Restore Revision)
```
[1] Load target revision data

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Create new revision (type: restore_from_revision)
    │
    [4] Update service with revision data
    │
COMMIT ───────────────────────────────────────────────

[5] Dispatch ServiceRevisionRestored event
```

#### 15.5 حذف نسخ قديمة (Cleanup Old Revisions)
- تُنفذ تلقائياً عبر scheduled job
- الاحتفاظ بآخر N نسخة (افتراضي: 50)
- الاحتفاظ بنسخ النشر دائماً

---

## 📌 العملية 16: الترجمة (Create Translation Version)

### 1. اسم العملية
`service.create_translation`

### 2. الهدف
إنشاء نسخة مترجمة من الخدمة للغة جديدة.

### 3. الشروط المسبقة
- الخدمة موجودة
- اللغة المستهدفة مفعلة في النظام
- لا توجد ترجمة سابقة لهذه اللغة
- صلاحية `service.translate`

### 4. خطوات التنفيذ

```
[1] Validate target locale is enabled

[2] Check translation doesn't exist

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Create translation record
    │   └── INSERT INTO service_translations (service_id, locale, ...)
    │
    [5] Copy non-translatable data from source
    │
    [6] Create Revision (type: translation_added)
    │
COMMIT ───────────────────────────────────────────────

[7] Dispatch ServiceTranslationCreated event
```

### 5. API Endpoint

```http
POST /api/v1/services/{id}/translations
{
  "locale": "fr",
  "copy_from": "en",
  "data": { ... }
}
```

---

## 📌 العملية 17: ربط/فصل الوسائط (Attach/Detach Media)

### 1. اسم العملية
`service.manage_media`

### 2. العمليات الفرعية

#### 17.1 ربط وسائط (Attach Media)
```
[1] Validate media exists and accessible

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Insert media relation
    │   └── INSERT INTO service_media (service_id, media_id, collection, order)
    │
    [4] Create Revision
    │
COMMIT ───────────────────────────────────────────────
```

#### 17.2 فصل وسائط (Detach Media)
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Delete media relation
    │   └── DELETE FROM service_media WHERE service_id = ? AND media_id = ?
    │
    [3] Create Revision
    │
COMMIT ───────────────────────────────────────────────
```

#### 17.3 إعادة ترتيب (Reorder Media)
```http
PUT /api/v1/services/{id}/media/reorder
{
  "media": ["uuid1", "uuid2", "uuid3"]
}
```

---

## 📌 العملية 18: إدارة العلاقات (Relations Management)

### 1. اسم العملية
`service.manage_relations`

### 2. العمليات الفرعية
- ربط بتصنيفات (Attach Categories)
- فصل من تصنيفات (Detach Categories)
- ربط خدمات مرتبطة (Link Related Services)
- ربط بصفحات (Link to Pages)

### 3. خطوات التنفيذ (مثال: ربط تصنيف)

```
[1] Validate category exists

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Sync category relations
    │
    [4] Create Revision
    │
COMMIT ───────────────────────────────────────────────

[5] Dispatch ServiceRelationsUpdated event
```

---

## 📌 العملية 19: الفهرسة (Indexing)

### 1. اسم العملية
`service.index`

### 2. الهدف
فهرسة الخدمة في محرك البحث.

### 3. خطوات التنفيذ

```
[1] Load service with all relations

[2] Transform to searchable document
    ├── Include all translations
    ├── Include category names
    └── Include metadata

[3] Send to search engine
    └── POST to Elasticsearch/Meilisearch/Algolia

[4] Update indexed_at timestamp
```

### 4. Background Processing
- تُنفذ دائماً عبر Queue
- Batch indexing للعمليات الجماعية
- Retry on failure: 3 times with backoff

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                     Service Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Author                    System                    Reviewer    │
│    │                         │                          │        │
│    │──── Create Service ────▶│                          │        │
│    │◀─── Return Draft ───────│                          │        │
│    │                         │                          │        │
│    │──── Update Draft ──────▶│                          │        │
│    │     (multiple times)    │                          │        │
│    │                         │                          │        │
│    │──── Submit for Review ─▶│                          │        │
│    │                         │──── Notify Reviewers ───▶│        │
│    │                         │                          │        │
│    │                         │◀─── Start Review ────────│        │
│    │◀─── Notify in Review ───│                          │        │
│    │                         │                          │        │
│    │                         │◀─── Approve ─────────────│        │
│    │◀─── Notify Approved ────│                          │        │
│    │                         │                          │        │
│    │──── Publish ───────────▶│                          │        │
│    │                         │──── Index + Cache ───────│        │
│    │                         │──── Notify Subscribers ──│        │
│    │                         │                          │        │
│    │──── Update (unpublish)─▶│                          │        │
│    │──── Republish ─────────▶│                          │        │
│    │                         │                          │        │
│    │──── Archive ───────────▶│                          │        │
│    │                         │                          │        │
│    │──── Soft Delete ───────▶│                          │        │
│    │                         │──── Schedule Permanent ──│        │
│    │                         │      Delete (30 days)    │        │
│    │                         │                          │        │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الخدمات**
