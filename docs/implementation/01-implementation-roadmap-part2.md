# خطة تنفيذ الكود التفصيلية - الجزء الثاني
## Implementation Roadmap (35% → 75%)

---

# Phase 3: Content System (35% - 55%)

## Sprint 4: Content & Taxonomy Modules

### الأسبوع 5 - اليوم 3-5: Content Module (Core)

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.1 | إنشاء Content Module | `modules/Content/` | 🔴 Critical |
| 3.2 | Article Model | `modules/Content/Domain/Models/Article.php` | 🔴 Critical |
| 3.3 | ArticleTranslation Model | `modules/Content/Domain/Models/ArticleTranslation.php` | 🔴 Critical |
| 3.4 | Page Model | `modules/Content/Domain/Models/Page.php` | 🔴 Critical |
| 3.5 | PageTranslation Model | `modules/Content/Domain/Models/PageTranslation.php` | 🔴 Critical |
| 3.6 | Service Model | `modules/Content/Domain/Models/Service.php` | 🟡 High |
| 3.7 | Content Migrations | `modules/Content/Database/Migrations/` | 🔴 Critical |

**Schema - articles table:**
```
articles
├── id (uuid, PK)
├── author_id (FK -> users)
├── featured_image_id (FK -> media, nullable)
├── type (enum: post, news, tutorial)
├── status (enum: draft, pending, published, archived)
├── is_featured (boolean)
├── is_commentable (boolean)
├── view_count (integer)
├── reading_time (integer, minutes)
├── published_at (timestamp, nullable)
├── created_at
├── updated_at
└── deleted_at
```

**Schema - article_translations table:**
```
article_translations
├── id (uuid, PK)
├── article_id (FK)
├── locale (string)
├── title
├── slug (unique per locale)
├── excerpt (text, nullable)
├── content (longtext)
├── meta_title (nullable)
├── meta_description (nullable)
├── meta_keywords (nullable)
├── created_at
└── updated_at
└── UNIQUE(article_id, locale)
```

---

### الأسبوع 6 - اليوم 1-2: Content Services

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.8 | ArticleService | `modules/Content/Services/ArticleService.php` | 🔴 Critical |
| 3.9 | ArticleRepository | `modules/Content/Repositories/ArticleRepository.php` | 🔴 Critical |
| 3.10 | PageService | `modules/Content/Services/PageService.php` | 🔴 Critical |
| 3.11 | ContentPublisher | `modules/Content/Services/ContentPublisher.php` | 🔴 Critical |
| 3.12 | SlugGenerator | `modules/Content/Services/SlugGenerator.php` | 🟡 High |

**ArticleService methods:**
```php
interface ArticleServiceContract
{
    public function create(CreateArticleDTO $data): Article;
    public function update(Article $article, UpdateArticleDTO $data): Article;
    public function publish(Article $article): Article;
    public function unpublish(Article $article): Article;
    public function schedule(Article $article, Carbon $publishAt): Article;
    public function archive(Article $article): Article;
    public function delete(Article $article): bool;
    public function duplicate(Article $article): Article;
}
```

---

### الأسبوع 6 - اليوم 3-4: Taxonomy Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.13 | إنشاء Taxonomy Module | `modules/Taxonomy/` | 🔴 Critical |
| 3.14 | TaxonomyType Model | `modules/Taxonomy/Domain/Models/TaxonomyType.php` | 🔴 Critical |
| 3.15 | Taxonomy Model | `modules/Taxonomy/Domain/Models/Taxonomy.php` | 🔴 Critical |
| 3.16 | TaxonomyTranslation | `modules/Taxonomy/Domain/Models/TaxonomyTranslation.php` | 🔴 Critical |
| 3.17 | Taggable Trait (polymorphic) | `modules/Taxonomy/Traits/HasTaxonomies.php` | 🔴 Critical |
| 3.18 | TaxonomyService | `modules/Taxonomy/Services/TaxonomyService.php` | 🔴 Critical |

**Schema - taxonomy_types table:**
```
taxonomy_types
├── id (uuid, PK)
├── slug (unique: category, tag, industry)
├── name (json: {ar: '', en: ''})
├── is_hierarchical (boolean)
├── is_multiple (boolean)
├── applies_to (json: ['articles', 'products'])
├── created_at
└── updated_at
```

**Schema - taxonomies table:**
```
taxonomies
├── id (uuid, PK)
├── type_id (FK -> taxonomy_types)
├── parent_id (FK -> taxonomies, nullable, self-ref)
├── featured_image_id (FK -> media, nullable)
├── ordering (integer)
├── is_active (boolean)
├── created_at
├── updated_at
└── deleted_at
```

**Schema - taggables (pivot):**
```
taggables
├── taxonomy_id (FK)
├── taggable_id (uuid)
├── taggable_type (string: article, product, etc.)
└── PRIMARY KEY(taxonomy_id, taggable_id, taggable_type)
```

---

### الأسبوع 6 - اليوم 5: Menu Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.19 | إنشاء Menu Module | `modules/Menu/` | 🟡 High |
| 3.20 | Menu Model | `modules/Menu/Domain/Models/Menu.php` | 🟡 High |
| 3.21 | MenuItem Model | `modules/Menu/Domain/Models/MenuItem.php` | 🟡 High |
| 3.22 | MenuService | `modules/Menu/Services/MenuService.php` | 🟡 High |
| 3.23 | Menu Builder | `modules/Menu/Services/MenuBuilder.php` | 🟡 High |

**Schema - menus table:**
```
menus
├── id (uuid, PK)
├── slug (unique)
├── name
├── location (header, footer, sidebar)
├── is_active (boolean)
├── created_at
└── updated_at
```

**Schema - menu_items table:**
```
menu_items
├── id (uuid, PK)
├── menu_id (FK)
├── parent_id (FK, nullable, self-ref)
├── type (enum: page, article, taxonomy, custom, module)
├── linkable_id (nullable)
├── linkable_type (nullable)
├── title (json: {ar: '', en: ''})
├── url (nullable, for custom)
├── target (_self, _blank)
├── icon (nullable)
├── css_class (nullable)
├── ordering (integer)
├── is_active (boolean)
├── conditions (json, nullable)
├── created_at
└── updated_at
```

---

## Sprint 5: Search & Revisions

### الأسبوع 7 - اليوم 1-3: Search Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.24 | إنشاء Search Module | `modules/Search/` | 🔴 Critical |
| 3.25 | SearchEngineContract | `modules/Search/Contracts/SearchEngineContract.php` | 🔴 Critical |
| 3.26 | MeilisearchAdapter | `modules/Search/Adapters/MeilisearchAdapter.php` | 🔴 Critical |
| 3.27 | Searchable Trait | `modules/Search/Traits/Searchable.php` | 🔴 Critical |
| 3.28 | SearchService | `modules/Search/Services/SearchService.php` | 🔴 Critical |
| 3.29 | IndexContent Job | `modules/Search/Jobs/IndexContent.php` | 🟡 High |
| 3.30 | search:reindex Command | `modules/Search/Console/Commands/ReindexCommand.php` | 🟡 High |

**SearchEngineContract:**
```php
interface SearchEngineContract
{
    public function index(string $index, string $id, array $data): bool;
    public function delete(string $index, string $id): bool;
    public function search(string $index, array $query): array;
    public function bulk(string $index, array $operations): bool;
    public function createIndex(string $index, array $settings = []): bool;
    public function deleteIndex(string $index): bool;
}
```

---

### الأسبوع 7 - اليوم 4-5: Revisions System

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 3.31 | Revision Model | `modules/Content/Domain/Models/Revision.php` | 🟡 High |
| 3.32 | HasRevisions Trait | `modules/Content/Traits/HasRevisions.php` | 🟡 High |
| 3.33 | RevisionService | `modules/Content/Services/RevisionService.php` | 🟡 High |
| 3.34 | RevisionComparer | `modules/Content/Services/RevisionComparer.php` | 🟢 Medium |

**Schema - revisions table:**
```
revisions
├── id (uuid, PK)
├── revisionable_id (uuid)
├── revisionable_type (string)
├── user_id (FK -> users)
├── revision_number (integer)
├── data (json - full snapshot)
├── changes (json - diff only)
├── summary (nullable)
├── is_auto (boolean)
├── created_at
└── INDEX(revisionable_type, revisionable_id)
```

---

## ✅ مخرجات Phase 3 (Checkpoint 55%)

```
modules/
├── Content/        ✓ NEW
│   ├── Domain/Models/
│   │   ├── Article.php
│   │   ├── ArticleTranslation.php
│   │   ├── Page.php
│   │   ├── PageTranslation.php
│   │   ├── Service.php
│   │   └── Revision.php
│   ├── Services/
│   │   ├── ArticleService.php
│   │   ├── PageService.php
│   │   ├── ContentPublisher.php
│   │   ├── SlugGenerator.php
│   │   └── RevisionService.php
│   ├── Repositories/
│   ├── Http/Controllers/Api/
│   ├── Events/
│   │   ├── ArticleCreated.php
│   │   ├── ArticlePublished.php
│   │   └── ArticleDeleted.php
│   └── Traits/HasRevisions.php
├── Taxonomy/       ✓ NEW
│   ├── Domain/Models/
│   │   ├── TaxonomyType.php
│   │   ├── Taxonomy.php
│   │   └── TaxonomyTranslation.php
│   ├── Services/TaxonomyService.php
│   └── Traits/HasTaxonomies.php
├── Menu/           ✓ NEW
│   ├── Domain/Models/
│   │   ├── Menu.php
│   │   └── MenuItem.php
│   └── Services/
│       ├── MenuService.php
│       └── MenuBuilder.php
└── Search/         ✓ NEW
    ├── Contracts/SearchEngineContract.php
    ├── Adapters/MeilisearchAdapter.php
    ├── Services/SearchService.php
    ├── Jobs/IndexContent.php
    └── Traits/Searchable.php
```

**اختبارات التحقق:**
- [ ] إنشاء مقال مع ترجمات ✓
- [ ] نشر/إلغاء نشر مقال ✓
- [ ] ربط مقال بتصنيفات ✓
- [ ] البحث يعمل ✓
- [ ] القوائم تعمل ✓
- [ ] المراجعات تُحفظ ✓

---

# Phase 4: Extended Features (55% - 75%)

## Sprint 6: Forms & Comments

### الأسبوع 8 - اليوم 1-3: Forms Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 4.1 | إنشاء Forms Module | `modules/Forms/` | 🟡 High |
| 4.2 | Form Model | `modules/Forms/Domain/Models/Form.php` | 🟡 High |
| 4.3 | FormField Model | `modules/Forms/Domain/Models/FormField.php` | 🟡 High |
| 4.4 | FormSubmission Model | `modules/Forms/Domain/Models/FormSubmission.php` | 🟡 High |
| 4.5 | FormService | `modules/Forms/Services/FormService.php` | 🟡 High |
| 4.6 | SpamDetector | `modules/Forms/Services/SpamDetector.php` | 🟡 High |
| 4.7 | FormValidator | `modules/Forms/Services/FormValidator.php` | 🟡 High |
| 4.8 | ProcessSubmission Job | `modules/Forms/Jobs/ProcessSubmission.php` | 🟡 High |

**Schema - forms table:**
```
forms
├── id (uuid, PK)
├── slug (unique)
├── name
├── description (nullable)
├── type (contact, newsletter, survey, custom)
├── success_message (json)
├── notification_emails (json)
├── redirect_url (nullable)
├── is_active (boolean)
├── captcha_enabled (boolean)
├── settings (json)
├── created_at
└── updated_at
```

**Schema - form_fields table:**
```
form_fields
├── id (uuid, PK)
├── form_id (FK)
├── name
├── label (json)
├── type (text, email, textarea, select, checkbox, file)
├── placeholder (json, nullable)
├── default_value (nullable)
├── options (json, for select/radio)
├── validation_rules (json)
├── is_required (boolean)
├── ordering (integer)
├── conditions (json, nullable)
├── created_at
└── updated_at
```

**Schema - form_submissions table:**
```
form_submissions
├── id (uuid, PK)
├── form_id (FK)
├── user_id (FK, nullable)
├── data (json - submitted values)
├── ip_address
├── user_agent
├── referrer (nullable)
├── status (new, read, spam, processed)
├── notes (text, nullable)
├── processed_at (nullable)
├── created_at
└── updated_at
```

---

### الأسبوع 8 - اليوم 4-5: Comments Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 4.9 | إنشاء Comments Module | `modules/Comments/` | 🟢 Medium |
| 4.10 | Comment Model | `modules/Comments/Domain/Models/Comment.php` | 🟢 Medium |
| 4.11 | HasComments Trait | `modules/Comments/Traits/HasComments.php` | 🟢 Medium |
| 4.12 | CommentService | `modules/Comments/Services/CommentService.php` | 🟢 Medium |
| 4.13 | CommentModerator | `modules/Comments/Services/CommentModerator.php` | 🟢 Medium |

**Schema - comments table:**
```
comments
├── id (uuid, PK)
├── commentable_id (uuid)
├── commentable_type (string)
├── parent_id (FK, nullable, for replies)
├── user_id (FK, nullable)
├── author_name (for guests)
├── author_email (for guests)
├── content (text)
├── status (pending, approved, spam, rejected)
├── ip_address
├── user_agent
├── likes_count (integer)
├── is_pinned (boolean)
├── approved_at (nullable)
├── approved_by (FK, nullable)
├── created_at
├── updated_at
└── deleted_at
```

---

## Sprint 7: Notifications & Analytics

### الأسبوع 9 - اليوم 1-3: Notifications Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 4.14 | إنشاء Notifications Module | `modules/Notifications/` | 🟡 High |
| 4.15 | Notification Model | `modules/Notifications/Domain/Models/Notification.php` | 🟡 High |
| 4.16 | NotificationTemplate Model | `modules/Notifications/Domain/Models/NotificationTemplate.php` | 🟡 High |
| 4.17 | NotificationService | `modules/Notifications/Services/NotificationService.php` | 🟡 High |
| 4.18 | EmailChannel | `modules/Notifications/Channels/EmailChannel.php` | 🟡 High |
| 4.19 | DatabaseChannel | `modules/Notifications/Channels/DatabaseChannel.php` | 🟡 High |
| 4.20 | SendNotification Job | `modules/Notifications/Jobs/SendNotification.php` | 🟡 High |

---

### الأسبوع 9 - اليوم 4-5: Analytics Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 4.21 | إنشاء Analytics Module | `modules/Analytics/` | 🟢 Medium |
| 4.22 | PageView Model | `modules/Analytics/Domain/Models/PageView.php` | 🟢 Medium |
| 4.23 | ActivityLog Model | `modules/Analytics/Domain/Models/ActivityLog.php` | 🟢 Medium |
| 4.24 | AnalyticsService | `modules/Analytics/Services/AnalyticsService.php` | 🟢 Medium |
| 4.25 | TrackPageView Middleware | `modules/Analytics/Http/Middleware/TrackPageView.php` | 🟢 Medium |
| 4.26 | LogActivity Trait | `modules/Analytics/Traits/LogsActivity.php` | 🟢 Medium |

**Schema - page_views table:**
```
page_views
├── id (uuid, PK)
├── viewable_id (uuid, nullable)
├── viewable_type (string, nullable)
├── url
├── user_id (FK, nullable)
├── session_id
├── ip_address
├── user_agent
├── referrer (nullable)
├── country (nullable)
├── device_type (desktop, mobile, tablet)
├── created_at
└── INDEX(created_at)
└── INDEX(viewable_type, viewable_id)
```

**Schema - activity_logs table:**
```
activity_logs
├── id (uuid, PK)
├── user_id (FK, nullable)
├── subject_id (uuid, nullable)
├── subject_type (string, nullable)
├── action (created, updated, deleted, published, etc.)
├── description
├── properties (json - before/after)
├── ip_address
├── user_agent
├── created_at
└── INDEX(subject_type, subject_id)
└── INDEX(user_id)
└── INDEX(created_at)
```

---

## ✅ مخرجات Phase 4 (Checkpoint 75%)

```
modules/
├── Forms/          ✓ NEW
│   ├── Domain/Models/
│   │   ├── Form.php
│   │   ├── FormField.php
│   │   └── FormSubmission.php
│   ├── Services/
│   │   ├── FormService.php
│   │   ├── FormValidator.php
│   │   └── SpamDetector.php
│   └── Jobs/ProcessSubmission.php
├── Comments/       ✓ NEW
│   ├── Domain/Models/Comment.php
│   ├── Services/
│   │   ├── CommentService.php
│   │   └── CommentModerator.php
│   └── Traits/HasComments.php
├── Notifications/  ✓ NEW
│   ├── Domain/Models/
│   │   ├── Notification.php
│   │   └── NotificationTemplate.php
│   ├── Services/NotificationService.php
│   ├── Channels/
│   │   ├── EmailChannel.php
│   │   └── DatabaseChannel.php
│   └── Jobs/SendNotification.php
└── Analytics/      ✓ NEW
    ├── Domain/Models/
    │   ├── PageView.php
    │   └── ActivityLog.php
    ├── Services/AnalyticsService.php
    ├── Http/Middleware/TrackPageView.php
    └── Traits/LogsActivity.php
```

**اختبارات التحقق:**
- [ ] إنشاء نموذج وإرساله ✓
- [ ] فلترة السبام تعمل ✓
- [ ] التعليقات تعمل ✓
- [ ] الإشعارات تُرسل ✓
- [ ] تتبع الزيارات يعمل ✓
- [ ] سجل النشاط يُسجل ✓

---

# ملخص التقدم حتى 75%

| Module | الحالة | المهام | المكتمل |
|--------|--------|--------|---------|
| Core | ✅ | 5 | 100% |
| Users | ✅ | 7 | 100% |
| Auth | ✅ | 8 | 100% |
| Media | ✅ | 8 | 100% |
| Localization | ✅ | 7 | 100% |
| Currency | ✅ | 5 | 100% |
| Content | ✅ | 13 | 100% |
| Taxonomy | ✅ | 6 | 100% |
| Menu | ✅ | 5 | 100% |
| Search | ✅ | 7 | 100% |
| Forms | ✅ | 8 | 100% |
| Comments | ✅ | 5 | 100% |
| Notifications | ✅ | 7 | 100% |
| Analytics | ✅ | 6 | 100% |

**إجمالي الـ Modules:** 14 module  
**إجمالي المهام:** ~97 مهمة

---

**يتبع الجزء الثالث: Admin Panel, API, Testing, Deployment (75% → 100%)**
