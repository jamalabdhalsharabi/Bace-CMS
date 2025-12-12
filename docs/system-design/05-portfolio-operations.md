# تحليل عمليات المشاريع والأعمال (Portfolio/Project Operations)

## 📋 نظرة عامة
المشاريع هي كيانات تعرض أعمال المؤسسة السابقة، تدعم معارض الصور، الفيديوهات، ودراسات الحالة. تستخدم لبناء المصداقية وعرض الخبرات.

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
                              ┌─────────────┐    ┌───────────┐
                              │ unpublished │───▶│ archived  │
                              └─────────────┘    └───────────┘
                                                       │
                                                       ▼
                                               ┌──────────────┐
                                               │ soft_deleted │
                                               └──────────────┘
```

---

## 📌 العملية 1: إنشاء مشروع (Create Project)

### 1. اسم العملية
`project.create`

### 2. الهدف
إنشاء مشروع جديد في معرض الأعمال مع دعم الوسائط المتعددة.

### 3. الشروط المسبقة
- صلاحية `project.create`
- التصنيفات (الصناعة، نوع المشروع) موجودة
- الوسائط متاحة

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate client information
    ├── Validate date range (start <= end)
    ├── Validate technologies/skills exist
    └── Validate media files

[2] Authorization Check
    └── Gate::authorize('project.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Project Record
    │   └── INSERT INTO projects (id, client_id, project_type, start_date, end_date, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO project_translations (project_id, locale, title, slug, description, challenge, solution, results, ...)
    │
    [7] Create Initial Revision
    │
    [8] Sync Categories/Industries
    │   └── INSERT INTO project_industries
    │
    [9] Sync Technologies/Skills
    │   └── INSERT INTO project_technologies
    │
    [10] Process Media Gallery
    │    ├── Before/After images
    │    ├── Process screenshots
    │    └── Embed videos
    │
    [11] Link Team Members (if applicable)
    │    └── INSERT INTO project_team
    │
    [12] Link Testimonials
    │    └── INSERT INTO project_testimonials
    │
COMMIT TRANSACTION ───────────────────────────────────

[13] Dispatch Events
     └── ProjectCreated event

[14] Queue Jobs
     ├── GenerateThumbnailsJob
     ├── OptimizeGalleryImagesJob
     └── IndexSearchJob
```

### 5. الآثار الجانبية
- إنشاء سجل المشروع
- توليد صور مصغرة
- ربط بالتقنيات والمهارات

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Invalid Date Range | Return 422 |
| Client Not Found | Create or return 422 |
| Technology Not Found | Create or return 422 |
| Media Processing Failed | Queue retry, continue |

### 7. Security Considerations
- التحقق من موافقة العميل على النشر
- إخفاء المعلومات الحساسة
- NDA compliance check

### 8. API Endpoint

```http
POST /api/v1/projects
Authorization: Bearer {token}
Content-Type: application/json

{
  "client_id": "uuid",
  "project_type": "web_development",
  "start_date": "2023-06-01",
  "end_date": "2023-12-15",
  "translations": {
    "ar": {
      "title": "مشروع تطوير موقع",
      "slug": "website-project",
      "description": "...",
      "challenge": "التحدي الذي واجهناه...",
      "solution": "الحل الذي قدمناه...",
      "results": "النتائج المحققة..."
    }
  },
  "industries": ["uuid1"],
  "technologies": ["react", "nodejs", "postgresql"],
  "gallery": [
    { "media_id": "uuid", "type": "screenshot", "order": 1 },
    { "media_id": "uuid", "type": "before_after", "before_id": "uuid1", "after_id": "uuid2" }
  ],
  "metrics": {
    "performance_improvement": "40%",
    "user_satisfaction": "95%"
  },
  "is_featured": false,
  "client_permission": true
}
```

### 9. Webhook Payload

```json
{
  "event": "project.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "type": "web_development",
    "client": "Client Name",
    "industries": ["Technology"],
    "technologies": ["react", "nodejs"],
    "duration_months": 6
  }
}
```

---

## 📌 العملية 2: تعديل مشروع (Update Project)

### 1. اسم العملية
`project.update`

### 2. خطوات التنفيذ

```
[1] Load project with relations

[2] Validate changes
    ├── Validate client permission still valid
    └── Validate new media

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Create Revision
    │
    [5] Update Project Record
    │
    [6] Sync Translations
    │
    [7] Sync Technologies
    │
    [8] Sync Industries
    │
    [9] Update Gallery
    │   ├── Add new media
    │   ├── Reorder existing
    │   └── Remove deleted
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Queue Jobs
     ├── ReindexSearchJob
     ├── InvalidateCacheJob
     └── RegenerateThumbnailsJob (if gallery changed)
```

---

## 📌 العمليات 3-14: دورة الحياة الأساسية

*نفس نمط الخدمات والمقالات*

---

## 📌 العمليات الخاصة بالمشاريع

### 15. تمييز المشروع (Feature Project)

```http
POST /api/v1/projects/{id}/feature
{
  "position": "homepage_hero",
  "order": 1
}
```

**خطوات التنفيذ:**
```
[1] Validate position exists

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Reorder existing featured projects
    │
    [4] Set is_featured = true
    │
    [5] Set featured_position, featured_order
    │
COMMIT ───────────────────────────────────────────────

[6] Queue InvalidateFeaturedCacheJob
```

### 16. ربط شهادة عميل (Link Testimonial)

```http
POST /api/v1/projects/{id}/testimonials
{
  "testimonial_id": "uuid",
  "is_primary": true
}
```

### 17. إدارة معرض الصور (Gallery Management)

#### 17.1 إضافة صورة
```http
POST /api/v1/projects/{id}/gallery
{
  "media_id": "uuid",
  "type": "screenshot",
  "caption": { "ar": "...", "en": "..." }
}
```

#### 17.2 إنشاء مقارنة Before/After
```http
POST /api/v1/projects/{id}/gallery/comparison
{
  "before_media_id": "uuid1",
  "after_media_id": "uuid2",
  "caption": { "ar": "قبل وبعد التطوير" }
}
```

#### 17.3 إعادة ترتيب المعرض
```http
PUT /api/v1/projects/{id}/gallery/reorder
{
  "items": ["uuid1", "uuid2", "uuid3"]
}
```

### 18. إدارة دراسة الحالة (Case Study)

```http
PUT /api/v1/projects/{id}/case-study
{
  "translations": {
    "ar": {
      "executive_summary": "...",
      "objectives": ["هدف 1", "هدف 2"],
      "methodology": "...",
      "timeline": [
        { "phase": "التخطيط", "duration": "2 weeks" }
      ],
      "key_findings": "...",
      "recommendations": "..."
    }
  }
}
```

### 19. إضافة مقاييس النتائج (Add Metrics)

```http
PUT /api/v1/projects/{id}/metrics
{
  "metrics": [
    { "name": "performance", "value": "40%", "type": "improvement" },
    { "name": "satisfaction", "value": "95%", "type": "score" },
    { "name": "roi", "value": "250%", "type": "percentage" }
  ]
}
```

### 20. ربط مشاريع مرتبطة (Link Related Projects)

```http
PUT /api/v1/projects/{id}/related
{
  "related_ids": ["uuid1", "uuid2"],
  "relation_type": "similar_technology"
}
```

### 21. تصدير كـ PDF (Export as PDF)

```http
POST /api/v1/projects/{id}/export-pdf
{
  "template": "case_study",
  "locale": "ar",
  "include_gallery": true
}
```

**خطوات التنفيذ:**
```
[1] Queue GenerateProjectPDFJob

[2] Job execution:
    ├── Load project with all relations
    ├── Render PDF template
    ├── Include optimized images
    └── Upload to storage

[3] Return download URL
```

### 22. طلب شهادة من العميل (Request Testimonial)

```http
POST /api/v1/projects/{id}/request-testimonial
{
  "client_contact_email": "client@example.com",
  "message": "نود الحصول على رأيكم..."
}
```

**خطوات التنفيذ:**
```
[1] Generate unique testimonial submission link

[2] Send email to client

[3] Track request status
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                     Project Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Manager           System            Client           Visitor    │
│    │                 │                  │                │       │
│    │── Create ──────▶│                  │                │       │
│    │                 │                  │                │       │
│    │── Add Gallery ─▶│                  │                │       │
│    │── Add Case Study│                  │                │       │
│    │── Add Metrics ─▶│                  │                │       │
│    │                 │                  │                │       │
│    │── Request ─────▶│── Send Email ───▶│                │       │
│    │   Testimonial   │                  │                │       │
│    │                 │◀── Submit ───────│                │       │
│    │◀── Link ────────│   Testimonial    │                │       │
│    │                 │                  │                │       │
│    │── Publish ─────▶│                  │                │       │
│    │                 │── Index ─────────│                │       │
│    │                 │                  │                │       │
│    │── Feature ─────▶│                  │                │       │
│    │                 │── Update Home ───│                │       │
│    │                 │                  │       ◀── View─│       │
│    │                 │                  │                │       │
│    │── Export PDF ──▶│                  │                │       │
│    │◀── Download ────│                  │                │       │
│    │                 │                  │                │       │
│    │── Archive ─────▶│                  │                │       │
│    │                 │                  │                │       │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات المشاريع**
