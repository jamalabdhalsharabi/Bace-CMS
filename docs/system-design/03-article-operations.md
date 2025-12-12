# تحليل عمليات المقالات والأخبار (Article/Blog Operations)

## 📋 نظرة عامة
المقالات هي محتوى ديناميكي يتضمن الأخبار والمدونات والمقالات التعليمية. تدعم التصنيفات، الوسوم، التعليقات، والمشاركة الاجتماعية.

---

## 🔄 State Machine Diagram

```
┌──────────┐    save     ┌──────────┐   submit    ┌─────────────────┐
│  (new)   │────────────▶│  draft   │────────────▶│ pending_review  │
└──────────┘             └──────────┘             └─────────────────┘
                              ▲                          │
                              │          ┌───────────────┼───────────────┐
                              │          ▼               ▼               ▼
                         ┌────────┐ ┌──────────┐   ┌──────────┐   ┌───────────┐
                         │rejected│ │in_review │   │ approved │   │ scheduled │
                         └────────┘ └──────────┘   └──────────┘   └───────────┘
                              ▲          │               │               │
                              └──────────┘               │               │
                                                         ▼               ▼
                                                   ┌───────────────────────┐
                                                   │      published        │
                                                   └───────────────────────┘
                                                              │
                                         ┌────────────────────┼────────────────────┐
                                         ▼                    ▼                    ▼
                                   ┌───────────┐       ┌───────────┐       ┌──────────────┐
                                   │unpublished│       │ archived  │       │ soft_deleted │
                                   └───────────┘       └───────────┘       └──────────────┘
```

---

## 📌 العملية 1: إنشاء مقال (Create Article)

### 1. اسم العملية
`article.create`

### 2. الهدف
إنشاء مقال جديد مع دعم الوسائط المتعددة والتصنيفات.

### 3. الشروط المسبقة
- المستخدم مصادق ولديه صلاحية `article.create`
- التصنيفات المختارة موجودة
- الوسوم صالحة

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Sanitize content (XSS protection)
    ├── Validate categories exist
    ├── Validate/create tags
    └── Validate featured image

[2] Authorization Check
    └── Gate::authorize('article.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Article Record
    │   └── INSERT INTO articles (id, author_id, type, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO article_translations (article_id, locale, title, slug, content, excerpt, ...)
    │
    [7] Create Initial Revision
    │
    [8] Sync Categories
    │   └── INSERT INTO article_category (article_id, category_id)
    │
    [9] Sync Tags
    │   ├── Create new tags if needed
    │   └── INSERT INTO article_tag (article_id, tag_id)
    │
    [10] Process Media
    │    ├── Featured image
    │    ├── Gallery images
    │    └── Content embedded media
    │
COMMIT TRANSACTION ───────────────────────────────────

[11] Dispatch Events
     └── ArticleCreated event

[12] Queue Jobs
     ├── ExtractContentMediaJob (parse content for media)
     ├── GenerateExcerptJob (if auto-excerpt enabled)
     └── CalculateReadingTimeJob
```

### 5. الآثار الجانبية
- إنشاء سجل المقال
- إنشاء/ربط الوسوم
- حساب وقت القراءة
- استخراج الوسائط من المحتوى

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Invalid Category | Return 422 + valid categories |
| Duplicate Slug | Auto-append unique suffix |
| Content Too Long | Return 422 + max length |
| Invalid Tags | Create or suggest alternatives |

### 7. Idempotency & Concurrency
- `X-Idempotency-Key` header
- Slug uniqueness check
- Tag creation with upsert

### 8. Security Considerations
- تنظيف HTML (allowlist للعناصر المسموحة)
- فحص الروابط الخارجية
- منع script injection
- التحقق من ملكية الوسائط

### 9. Observability

```yaml
metrics:
  - article.create.count
  - article.create.duration_ms
  - article.create.word_count_avg
  - article.create.by_category

logs:
  fields:
    - word_count: N
    - categories: [...]
    - tags: [...]
    - has_featured_image: boolean
```

### 10. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Editor | ✅ |
| Author | ✅ |
| Contributor | ✅ (draft only) |

### 11. API Endpoint

```http
POST /api/v1/articles
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "blog_post",
  "translations": {
    "ar": {
      "title": "عنوان المقال",
      "slug": "article-slug",
      "content": "<p>محتوى المقال...</p>",
      "excerpt": "ملخص قصير"
    }
  },
  "categories": ["uuid1"],
  "tags": ["تقنية", "برمجة"],
  "featured_image_id": "media-uuid",
  "allow_comments": true,
  "status": "draft"
}
```

### 12. Webhook Payload

```json
{
  "event": "article.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "type": "blog_post",
    "author": {
      "id": "user-uuid",
      "name": "Author Name"
    },
    "categories": ["category-name"],
    "tags": ["tag1", "tag2"],
    "word_count": 1500,
    "reading_time_minutes": 6
  }
}
```

---

## 📌 العملية 2: تعديل مقال (Update Article)

### 1. اسم العملية
`article.update`

### 2. الهدف
تحديث محتوى المقال مع تتبع التغييرات.

### 3. الشروط المسبقة
- المقال موجود
- المستخدم مالك أو لديه صلاحية
- الحالة تسمح بالتعديل

### 4. خطوات التنفيذ

```
[1] Load article with lock

[2] Validate ownership or permission
    └── Author can edit own, Editor can edit all

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Create Revision (with diff)
    │   └── Store previous content for comparison
    │
    [5] Update Article Record
    │
    [6] Sync Translations
    │
    [7] Sync Categories
    │   └── Detach old, attach new
    │
    [8] Sync Tags
    │   └── Create new tags, detach removed
    │
    [9] Update Media Relations
    │
    [10] Recalculate Metadata
    │    ├── word_count
    │    ├── reading_time
    │    └── excerpt (if auto)
    │
COMMIT TRANSACTION ───────────────────────────────────

[11] Queue Jobs
     ├── ReindexSearchJob (if published)
     ├── InvalidateCacheJob
     ├── UpdateRSSFeedJob (if published)
     └── NotifySubscribersJob (if major update + published)
```

### 5. Implementation Notes
- تتبع التغييرات الجوهرية vs التعديلات الطفيفة
- خيار "إشعار المشتركين" للتحديثات الكبيرة

---

## 📌 العملية 3: حفظ مسودة (Save Draft)

*نفس النمط العام مع:*
- Auto-save كل 60 ثانية
- حفظ موقع المؤشر
- حفظ حالة التنسيق

---

## 📌 العمليات 4-7: دورة المراجعة

*نفس نمط الخدمات*

---

## 📌 العملية 8: النشر (Publish Article)

### 1. اسم العملية
`article.publish`

### 2. الهدف
نشر المقال وإتاحته للقراء.

### 3. خطوات التنفيذ

```
[1] Validate publishable
    ├── Required fields complete
    ├── Featured image present
    └── At least one category

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at (first time)
    │   └── Or updated_at for republish
    │
    [5] Generate canonical URL
    │
    [6] Create Revision (type: publish)
    │
COMMIT TRANSACTION ───────────────────────────────────

[7] Queue Jobs (HIGH PRIORITY)
    ├── IndexSearchJob
    ├── InvalidateCDNCacheJob
    ├── UpdateSitemapJob
    ├── UpdateRSSFeedJob
    ├── GenerateSocialImagesJob
    ├── PingSearchEnginesJob
    ├── NotifySubscribersJob
    ├── PostToSocialMediaJob (if configured)
    └── SendNewsletterJob (if configured)

[8] Dispatch ArticlePublished event
```

### 4. الآثار الجانبية
- تحديث RSS feed
- إنشاء صور للمشاركة الاجتماعية
- إشعار المشتركين
- النشر على وسائل التواصل (اختياري)

### 5. API Endpoint

```http
POST /api/v1/articles/{id}/publish
{
  "notify_subscribers": true,
  "post_to_social": ["twitter", "facebook"],
  "newsletter": false
}
```

---

## 📌 العملية 9: جدولة النشر (Schedule)

### 1. اسم العملية
`article.schedule`

### 2. خطوات التنفيذ

```
[1] Validate scheduled_at is future

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → scheduled
    │
    [4] Set scheduled_at
    │
    [5] Store publish options (social, newsletter, etc.)
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Delayed PublishArticleJob

[7] Queue ScheduleReminderJob (notify author before publish)
```

### 3. Implementation Notes
- إشعار المؤلف قبل النشر بـ 1 ساعة
- إمكانية إلغاء الجدولة

---

## 📌 العمليات 10-14: Unpublish, Archive, Restore, Delete

*نفس نمط الخدمات*

---

## 📌 العملية 15: إدارة النسخ (Revisions)

*نفس نمط الخدمات مع:*
- مقارنة محتوى المقال (diff view)
- تتبع تغييرات الوسوم والتصنيفات

---

## 📌 العملية 16: الترجمة (Translation)

*نفس نمط الخدمات*

---

## 📌 العمليات الخاصة بالمقالات

### 17. إدارة التعليقات (Comments Management)

#### 17.1 تفعيل/تعطيل التعليقات
```http
PUT /api/v1/articles/{id}/comments-settings
{
  "allow_comments": true,
  "moderation_required": true,
  "close_after_days": 30
}
```

#### 17.2 إغلاق التعليقات
```http
POST /api/v1/articles/{id}/close-comments
```

**خطوات التنفيذ:**
```
[1] Update allow_comments → false

[2] Set comments_closed_at

[3] Optionally notify active commenters
```

### 18. تثبيت المقال (Pin/Feature Article)

```http
POST /api/v1/articles/{id}/pin
{
  "position": "hero",
  "duration_days": 7
}
```

**خطوات التنفيذ:**
```
[1] Validate position available

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Unpin current article in position (if any)
    │
    [4] Set is_pinned = true
    │
    [5] Set pin_position, pin_expires_at
    │
COMMIT ───────────────────────────────────────────────

[6] Queue InvalidateHomepageCacheJob

[7] Schedule UnpinArticleJob (at expiry)
```

### 19. المقالات المرتبطة (Related Articles)

#### 19.1 ربط يدوي
```http
PUT /api/v1/articles/{id}/related
{
  "related_ids": ["uuid1", "uuid2", "uuid3"]
}
```

#### 19.2 توليد تلقائي
```
[1] Analyze article content and tags

[2] Find articles with:
    ├── Same categories
    ├── Common tags
    └── Similar content (TF-IDF or embeddings)

[3] Cache related articles list

[4] Refresh periodically or on article update
```

### 20. تحليلات المقال (Article Analytics)

```http
GET /api/v1/articles/{id}/analytics
```

**البيانات المتاحة:**
- عدد المشاهدات
- متوسط وقت القراءة
- معدل الارتداد
- مصادر الزيارات
- المشاركات الاجتماعية
- عدد التعليقات

### 21. نسخ المقال (Clone Article)

```http
POST /api/v1/articles/{id}/clone
{
  "new_slug": "article-copy",
  "include_media": true,
  "include_tags": true
}
```

### 22. تحويل نوع المقال (Convert Type)

```http
PUT /api/v1/articles/{id}/convert
{
  "new_type": "news"
}
```

### 23. إعادة النشر (Republish)

```http
POST /api/v1/articles/{id}/republish
{
  "update_published_at": true,
  "notify_subscribers": false
}
```

**خطوات التنفيذ:**
```
[1] Validate currently published

[2] Update published_at (optional)

[3] Queue Jobs
    ├── InvalidateCacheJob
    ├── UpdateRSSFeedJob
    └── PingSearchEnginesJob (with updated timestamp)
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                     Article Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Author              System              Editor           Reader │
│    │                   │                   │                │    │
│    │── Create ────────▶│                   │                │    │
│    │                   │                   │                │    │
│    │── Add Content ───▶│                   │                │    │
│    │   + Media         │                   │                │    │
│    │   + Tags          │                   │                │    │
│    │                   │                   │                │    │
│    │── Submit ────────▶│                   │                │    │
│    │                   │── Notify ────────▶│                │    │
│    │                   │                   │                │    │
│    │                   │◀── Approve ───────│                │    │
│    │                   │                   │                │    │
│    │── Publish ───────▶│                   │                │    │
│    │                   │── Update RSS ─────│                │    │
│    │                   │── Index Search ───│                │    │
│    │                   │── Social Post ────│                │    │
│    │                   │── Notify Subs ────│───────────────▶│    │
│    │                   │                   │                │    │
│    │                   │                   │     ◀── View ──│    │
│    │                   │                   │     ◀── Comment│    │
│    │                   │                   │                │    │
│    │── Pin Article ───▶│                   │                │    │
│    │                   │── Update Home ────│                │    │
│    │                   │                   │                │    │
│    │── View Analytics ─│                   │                │    │
│    │◀── Stats ─────────│                   │                │    │
│    │                   │                   │                │    │
│    │── Archive ───────▶│                   │                │    │
│    │                   │── Remove from ────│                │    │
│    │                   │   listings        │                │    │
│    │                   │                   │                │    │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات المقالات**
