# تحليل عمليات التوصيات (Testimonial Operations)

## 📋 نظرة عامة
التوصيات هي شهادات العملاء والمستخدمين حول الخدمات أو المنتجات. تدعم التحقق والتقييم والربط بكيانات أخرى.

---

## 🔄 State Machine Diagram

```
┌──────────┐   submit    ┌─────────────────┐
│  (new)   │────────────▶│ pending_review  │
└──────────┘             └─────────────────┘
                                │
                    ┌───────────┼───────────┐
                    ▼           ▼           ▼
              ┌──────────┐ ┌──────────┐ ┌──────────┐
              │in_review │ │ approved │ │ rejected │
              └──────────┘ └──────────┘ └──────────┘
                    │           │
                    └─────┬─────┘
                          ▼
                   ┌───────────┐
                   │ published │
                   └───────────┘
                          │
              ┌───────────┼───────────┐
              ▼           ▼           ▼
       ┌───────────┐ ┌──────────┐ ┌──────────────┐
       │unpublished│ │ archived │ │ soft_deleted │
       └───────────┘ └──────────┘ └──────────────┘
```

---

## 📌 العملية 1: إنشاء توصية (Create Testimonial)

### 1. اسم العملية
`testimonial.create`

### 2. الهدف
إنشاء توصية جديدة من عميل أو مستخدم.

### 3. الشروط المسبقة
- صلاحية `testimonial.create` أو رابط إرسال عام
- بيانات العميل صالحة
- التقييم ضمن النطاق المسموح

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate rating (1-5)
    ├── Validate content length
    ├── Validate client information
    └── Spam detection check

[2] Determine source
    ├── Admin created
    ├── Client submission (via link)
    └── Automated request response

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Testimonial Record
    │   └── INSERT INTO testimonials (id, client_name, client_email, client_company, rating, source, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO testimonial_translations (testimonial_id, locale, content, position, ...)
    │
    [7] Link to Entity (if applicable)
    │   └── INSERT INTO testimonial_relations (testimonial_id, entity_type, entity_id)
    │
    [8] Process Client Photo
    │   └── Link or upload client avatar
    │
    [9] Create Initial Revision
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Dispatch Events
     └── TestimonialSubmitted event

[11] Queue Jobs
     ├── NotifyAdminForReviewJob
     ├── VerifyClientEmailJob (if client submitted)
     └── SpamAnalysisJob
```

### 5. الآثار الجانبية
- إنشاء سجل التوصية
- إرسال إشعار للمراجعة
- التحقق من البريد الإلكتروني

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Spam Detected | Flag for manual review |
| Invalid Rating | Return 422 |
| Duplicate Submission | Return 409 |
| Email Verification Failed | Mark as unverified |

### 7. Security Considerations
- CAPTCHA للإرسال العام
- Rate limiting (3 per email per day)
- تنظيف المحتوى من HTML
- فحص الروابط المشبوهة

### 8. API Endpoint

```http
POST /api/v1/testimonials
Authorization: Bearer {token}
Content-Type: application/json

{
  "client_name": "أحمد محمد",
  "client_email": "ahmed@example.com",
  "client_company": "شركة التقنية",
  "client_position": "مدير تقني",
  "rating": 5,
  "translations": {
    "ar": {
      "content": "خدمة ممتازة وفريق عمل محترف...",
      "title": "تجربة رائعة"
    }
  },
  "linked_to": {
    "type": "service",
    "id": "uuid"
  },
  "client_photo_id": "media-uuid"
}
```

### Public Submission Endpoint:
```http
POST /api/v1/testimonials/submit
{
  "token": "submission-token",
  ... testimonial data ...
}
```

### 9. Webhook Payload

```json
{
  "event": "testimonial.submitted",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "rating": 5,
    "source": "client_submission",
    "linked_to": {
      "type": "service",
      "id": "uuid"
    },
    "requires_review": true
  }
}
```

---

## 📌 العملية 2: مراجعة التوصية (Review Testimonial)

### 1. اسم العملية
`testimonial.review`

### 2. الهدف
مراجعة التوصية المقدمة والتحقق من صحتها.

### 3. خطوات التنفيذ

```
[1] Load testimonial

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → in_review
    │
    [4] Set reviewer_id
    │
COMMIT ───────────────────────────────────────────────

[5] Reviewer actions:
    ├── Verify client identity
    ├── Check for inappropriate content
    ├── Edit content (if needed with permission)
    └── Make decision
```

---

## 📌 العملية 3: الموافقة (Approve)

### 1. اسم العملية
`testimonial.approve`

### 2. خطوات التنفيذ

```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → approved
    │
    [3] Set approved_at, approved_by
    │
    [4] Mark as verified (if email verified)
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── SendThankYouEmailJob
    └── NotifyMarketingTeamJob
```

---

## 📌 العملية 4: الرفض (Reject)

### 1. اسم العملية
`testimonial.reject`

### 2. خطوات التنفيذ

```
[1] Validate rejection reason

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → rejected
    │
    [4] Store rejection reason
    │
COMMIT ───────────────────────────────────────────────

[5] Queue SendRejectionNoticeJob (optional)
```

---

## 📌 العملية 5: النشر (Publish)

### 1. اسم العملية
`testimonial.publish`

### 2. خطوات التنفيذ

```
[1] Validate approved status

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at
    │
    [5] Set display_order (if not set)
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── InvalidateCacheJob
    ├── UpdateAverageRatingJob (for linked entity)
    └── IndexSearchJob
```

---

## 📌 العمليات الخاصة بالتوصيات

### 6. تمييز التوصية (Feature Testimonial)

```http
POST /api/v1/testimonials/{id}/feature
{
  "position": "homepage",
  "order": 1
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Set is_featured = true
    │
    [3] Set featured_position, featured_order
    │
    [4] Reorder other featured testimonials
    │
COMMIT ───────────────────────────────────────────────

[5] Queue InvalidateFeaturedCacheJob
```

### 7. ربط بكيان (Link to Entity)

```http
POST /api/v1/testimonials/{id}/link
{
  "entity_type": "project",
  "entity_id": "uuid",
  "is_primary": true
}
```

### 8. التحقق من العميل (Verify Client)

```http
POST /api/v1/testimonials/{id}/verify
{
  "method": "email",
  "verified_by": "system"
}
```

**خطوات التنفيذ:**
```
[1] Send verification email to client

[2] Client clicks verification link

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Set is_verified = true
    │
    [5] Set verified_at
    │
    [6] Add verified badge
    │
COMMIT ───────────────────────────────────────────────
```

### 9. طلب توصية (Request Testimonial)

```http
POST /api/v1/testimonials/request
{
  "client_email": "client@example.com",
  "client_name": "...",
  "linked_to": {
    "type": "project",
    "id": "uuid"
  },
  "message": "..."
}
```

**خطوات التنفيذ:**
```
[1] Generate unique submission token

[2] Create pending request record

[3] Send email with submission link

[4] Track request status
    ├── pending
    ├── opened
    ├── submitted
    └── expired
```

### 10. تحديث التقييم العام (Update Average Rating)

```
[Triggered after publish/unpublish]

[1] Get linked entity

[2] Calculate new average
    └── AVG(rating) WHERE status = published AND linked_to = entity

[3] Update entity average_rating

[4] Dispatch RatingUpdated event
```

### 11. استيراد التوصيات (Import Testimonials)

```http
POST /api/v1/testimonials/import
{
  "source": "google_reviews",
  "credentials": {...}
}
```

**مصادر مدعومة:**
- Google Reviews
- Facebook Reviews
- Trustpilot
- CSV file

### 12. إعادة ترتيب (Reorder)

```http
PUT /api/v1/testimonials/reorder
{
  "testimonials": [
    { "id": "uuid1", "order": 1 },
    { "id": "uuid2", "order": 2 }
  ]
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                   Testimonial Lifecycle Flow                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin           System           Client          Reviewer       │
│    │               │                │                │           │
│    │── Request ───▶│── Email ──────▶│                │           │
│    │               │                │                │           │
│    │               │◀── Submit ─────│                │           │
│    │               │                │                │           │
│    │               │── Verify ─────▶│                │           │
│    │               │◀── Confirm ────│                │           │
│    │               │                │                │           │
│    │               │── Notify ──────────────────────▶│           │
│    │               │                │                │           │
│    │               │◀── Review ─────────────────────│           │
│    │               │◀── Approve ────────────────────│           │
│    │               │                │                │           │
│    │◀── Publish ───│                │                │           │
│    │               │── Thank You ──▶│                │           │
│    │               │                │                │           │
│    │── Feature ───▶│                │                │           │
│    │               │── Update Cache─│                │           │
│    │               │                │                │           │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات التوصيات**
