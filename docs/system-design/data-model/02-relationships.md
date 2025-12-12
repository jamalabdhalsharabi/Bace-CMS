# تحليل العلاقات بين الكيانات (Relationships Analysis)
## نظام إدارة المحتوى CMS - العلاقات

---

## 📋 أنواع العلاقات المستخدمة

| الرمز | النوع | الوصف |
|-------|------|-------|
| `1:1` | One-to-One | علاقة واحد لواحد |
| `1:N` | One-to-Many | علاقة واحد لمتعدد |
| `N:M` | Many-to-Many | علاقة متعدد لمتعدد |
| `P` | Polymorphic | علاقة متعددة الأشكال |
| `S` | Self-Referential | علاقة ذاتية |
| `?` | Optional | اختيارية |
| `!` | Required | إجبارية |

---

## 🔗 علاقات كيانات المحتوى الأساسية

### 1. Service (الخدمات)

```
services
├── 1:N → service_translations (!)
│         FK: service_id
├── N:M → taxonomies (via content_taxonomies) (?)
│         Polymorphic: taggable_type = 'service'
├── N:M → media (via content_media) (?)
│         Polymorphic: mediable_type = 'service'
├── N:M → services (self, via content_related) (?)
│         Polymorphic: relatable_type = 'service'
├── 1:N → revisions (?)
│         Polymorphic: revisionable_type = 'service'
├── 1:1 → seo_metas (?)
│         Polymorphic: seoable_type = 'service'
├── N:1 → users (created_by) (!)
├── N:1 → users (updated_by) (?)
└── N:1 → users (deleted_by) (?)
```

### 2. Page (الصفحات)

```
pages
├── 1:N → page_translations (!)
├── S:N → pages (parent_id - Self Reference) (?)
│         FK: parent_id → pages.id
├── N:M → taxonomies (via content_taxonomies) (?)
├── N:M → media (via content_media) (?)
├── N:M → static_blocks (via page_blocks) (?)
├── 1:N → revisions (P) (?)
├── 1:1 → seo_metas (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

### 3. Article (المقالات)

```
articles
├── 1:N → article_translations (!)
├── N:M → taxonomies (via content_taxonomies) (?)
│         - categories
│         - tags
├── N:M → media (via content_media) (?)
│         - featured_image
│         - gallery
├── N:M → articles (self, via content_related) (?)
├── 1:N → comments (P) (?)
├── 1:N → revisions (P) (?)
├── 1:1 → seo_metas (P) (?)
├── N:1 → users (author_id) (!)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

### 4. Product (المنتجات)

```
products
├── 1:N → product_translations (!)
├── 1:N → product_variants (?)
├── 1:N → product_prices (!)
├── 1:1 → product_inventories (?)
├── N:M → taxonomies (via content_taxonomies) (?)
├── N:M → media (via content_media) (?)
├── N:M → product_attributes (via product_attribute_values) (?)
├── N:M → products (self, via content_related) (?)
├── 1:N → comments (P) (?)
├── 1:N → revisions (P) (?)
├── 1:1 → seo_metas (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

### 5. Product Variant (متغيرات المنتجات)

```
product_variants
├── N:1 → products (!) 
│         FK: product_id
├── 1:N → variant_prices (!)
├── 1:1 → variant_inventories (?)
├── N:M → attribute_values (via variant_attribute_values) (!)
├── N:M → media (via content_media) (?)
└── 1:N → stock_reservations (?)
```

### 6. Project (المشاريع)

```
projects
├── 1:N → project_translations (!)
├── N:M → taxonomies (via content_taxonomies) (?)
│         - industries
│         - technologies
├── N:M → media (via content_media) (?)
│         - gallery
│         - before_after
├── N:M → testimonials (via project_testimonials) (?)
├── N:M → projects (self, via content_related) (?)
├── 1:N → revisions (P) (?)
├── 1:1 → seo_metas (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

### 7. Event (الفعاليات)

```
events
├── 1:N → event_translations (!)
├── 1:N → event_ticket_types (?)
├── 1:N → event_sessions (?)
├── N:M → event_speakers (via event_session_speakers) (?)
├── 1:N → event_registrations (?)
├── N:M → taxonomies (via content_taxonomies) (?)
├── N:M → media (via content_media) (?)
├── 1:N → revisions (P) (?)
├── 1:1 → seo_metas (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

### 8. Testimonial (التوصيات)

```
testimonials
├── 1:N → testimonial_translations (!)
├── N:M → services (via content_testimonials) (?)
├── N:M → products (via content_testimonials) (?)
├── N:M → projects (via content_testimonials) (?)
├── N:1 → media (client_photo) (?)
├── 1:N → revisions (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (verified_by) (?)
```

### 9. Static Block (الأقسام الثابتة)

```
static_blocks
├── 1:N → static_block_translations (!)
├── N:M → pages (via page_blocks) (?)
├── N:M → media (via content_media) (?)
├── 1:N → revisions (P) (?)
├── N:1 → users (created_by) (!)
└── N:1 → users (updated_by) (?)
```

---

## 🔗 علاقات التصنيفات

### 10. Taxonomy Type (أنواع التصنيفات)

```
taxonomy_types
├── 1:N → taxonomies (!)
│         FK: type_id
└── N:1 → users (created_by) (!)
```

### 11. Taxonomy (التصنيفات)

```
taxonomies
├── 1:N → taxonomy_translations (!)
├── N:1 → taxonomy_types (!)
│         FK: type_id
├── S:N → taxonomies (parent_id - Self Reference) (?)
│         FK: parent_id → taxonomies.id
├── N:M → services (via content_taxonomies) (P) (?)
├── N:M → articles (via content_taxonomies) (P) (?)
├── N:M → products (via content_taxonomies) (P) (?)
├── N:M → projects (via content_taxonomies) (P) (?)
├── N:M → events (via content_taxonomies) (P) (?)
├── N:1 → media (icon/image) (?)
└── N:1 → users (created_by) (!)
```

---

## 🔗 علاقات الوسائط

### 12. Media (الوسائط)

```
media
├── 1:N → media_translations (?)
├── 1:N → media_variants (?)
├── N:1 → media_folders (?)
│         FK: folder_id
├── N:M → services (via content_media) (P)
├── N:M → articles (via content_media) (P)
├── N:M → products (via content_media) (P)
├── N:M → projects (via content_media) (P)
├── N:M → events (via content_media) (P)
├── N:M → pages (via content_media) (P)
├── N:M → static_blocks (via content_media) (P)
└── N:1 → users (uploaded_by) (!)
```

### 13. Media Folder (مجلدات الوسائط)

```
media_folders
├── 1:N → media (?)
├── S:N → media_folders (parent_id - Self Reference) (?)
│         FK: parent_id → media_folders.id
└── N:1 → users (created_by) (!)
```

---

## 🔗 علاقات القوائم

### 14. Menu (القوائم)

```
menus
├── 1:N → menu_translations (!)
├── 1:N → menu_items (?)
├── N:1 → menu_locations (?)
│         FK: location_id
└── N:1 → users (created_by) (!)
```

### 15. Menu Item (عناصر القوائم)

```
menu_items
├── 1:N → menu_item_translations (!)
├── N:1 → menus (!)
│         FK: menu_id
├── S:N → menu_items (parent_id - Self Reference) (?)
│         FK: parent_id → menu_items.id
├── N:1 → pages (?) [Polymorphic linkable]
├── N:1 → articles (?) [Polymorphic linkable]
├── N:1 → taxonomies (?) [Polymorphic linkable]
└── N:1 → users (created_by) (!)
```

---

## 🔗 علاقات النماذج

### 16. Form (النماذج)

```
forms
├── 1:N → form_fields (!)
├── 1:N → form_submissions (?)
└── N:1 → users (created_by) (!)
```

### 17. Form Submission (الإرسالات)

```
form_submissions
├── N:1 → forms (!)
│         FK: form_id
├── 1:N → submission_field_values (!)
├── 1:N → submission_attachments (?)
├── N:1 → users (assigned_to) (?)
├── N:1 → users (submitted_by) (?)
└── 1:N → activity_logs (P) (?)
```

---

## 🔗 علاقات التعليقات

### 18. Comment (التعليقات) - Polymorphic

```
comments
├── P:1 → articles (commentable) (?)
├── P:1 → products (commentable) (?)
├── P:1 → events (commentable) (?)
├── S:N → comments (parent_id - Self Reference) (?)
│         FK: parent_id → comments.id (للردود)
├── 1:N → comment_votes (?)
├── 1:N → comment_reports (?)
├── N:1 → users (author) (?)
└── N:1 → users (approved_by) (?)
```

---

## 🔗 علاقات الفعاليات الموسعة

### 19. Event Ticket Type (أنواع التذاكر)

```
event_ticket_types
├── N:1 → events (!)
│         FK: event_id
├── 1:N → event_tickets (?)
└── 1:N → event_ticket_prices (!)
```

### 20. Event Session (الجلسات)

```
event_sessions
├── 1:N → event_session_translations (!)
├── N:1 → events (!)
│         FK: event_id
└── N:M → event_speakers (via event_session_speakers) (?)
```

### 21. Event Registration (التسجيلات)

```
event_registrations
├── N:1 → events (!)
│         FK: event_id
├── N:1 → users (?)
│         FK: user_id
├── 1:N → event_tickets (!)
├── 1:N → event_checkins (?)
├── N:1 → coupons (?)
└── 1:N → payments (?)
```

---

## 🔗 علاقات التسعير

### 22. Pricing Plan (خطط التسعير)

```
pricing_plans
├── 1:N → pricing_plan_translations (!)
├── 1:N → plan_prices (!)
├── 1:N → plan_features (?)
├── 1:N → plan_limits (?)
├── 1:N → subscriptions (?)
├── N:M → coupons (via plan_coupons) (?)
├── N:M → services (via service_plans) (?)
├── N:M → products (via product_plans) (?)
└── N:1 → users (created_by) (!)
```

### 23. Subscription (الاشتراكات)

```
subscriptions
├── N:1 → pricing_plans (!)
│         FK: plan_id
├── N:1 → users (!)
│         FK: user_id
├── 1:N → subscription_usages (?)
├── 1:N → payments (?)
├── N:1 → coupons (?)
│         FK: coupon_id
└── 1:N → activity_logs (P) (?)
```

### 24. Coupon (الكوبونات)

```
coupons
├── 1:N → coupon_usages (?)
├── N:M → pricing_plans (via plan_coupons) (?)
├── 1:N → subscriptions (?)
├── 1:N → event_registrations (?)
└── N:1 → users (created_by) (!)
```

---

## 🔗 علاقات المنتجات الموسعة

### 25. Product Inventory (المخزون)

```
product_inventories
├── 1:1 → products (!)
│         FK: product_id
├── 1:N → inventory_movements (?)
└── 1:N → stock_reservations (?)
```

### 26. Product Attribute (سمات المنتجات)

```
product_attributes
├── 1:N → product_attribute_translations (!)
├── 1:N → attribute_values (!)
└── N:M → products (via product_attribute_values) (?)
```

---

## 🔗 علاقات العملات

### 27. Currency (العملات)

```
currencies
├── 1:N → currency_translations (!)
├── 1:N → exchange_rates (as from_currency) (?)
├── 1:N → exchange_rates (as to_currency) (?)
├── 1:N → product_prices (?)
├── 1:N → plan_prices (?)
└── 1:N → event_ticket_prices (?)
```

### 28. Exchange Rate (أسعار الصرف)

```
exchange_rates
├── N:1 → currencies (from_currency) (!)
│         FK: from_currency_id
├── N:1 → currencies (to_currency) (!)
│         FK: to_currency_id
├── 1:N → exchange_rate_history (?)
└── N:1 → users (updated_by) (?)
```

---

## 🔗 علاقات اللغات

### 29. Language (اللغات)

```
languages
├── 1:N → translation_values (?)
├── 1:N → service_translations (?)
├── 1:N → article_translations (?)
├── 1:N → page_translations (?)
├── 1:N → product_translations (?)
├── ... (all translation tables)
└── N:1 → languages (fallback_language) (?)
│         FK: fallback_id → languages.id
```

### 30. Translation Key (مفاتيح الترجمة)

```
translation_keys
├── 1:N → translation_values (!)
└── N:1 → translation_groups (?)
│         FK: group_id
```

---

## 🔗 علاقات المستخدمين

### 31. User (المستخدمون)

```
users
├── 1:1 → user_profiles (?)
├── N:M → roles (via user_roles) (!)
├── 1:N → user_sessions (?)
├── 1:N → user_settings (?)
├── 1:N → subscriptions (?)
├── 1:N → event_registrations (?)
├── 1:N → comments (?)
├── 1:N → form_submissions (?)
├── 1:N → activity_logs (?)
├── 1:N → notifications (?)
├── 1:N → services (created) (?)
├── 1:N → articles (authored) (?)
├── 1:N → products (created) (?)
├── 1:N → pages (created) (?)
└── 1:N → media (uploaded) (?)
```

### 32. Role (الأدوار)

```
roles
├── N:M → permissions (via role_permissions) (!)
├── N:M → users (via user_roles) (?)
└── N:1 → users (created_by) (!)
```

### 33. Permission (الصلاحيات)

```
permissions
├── N:M → roles (via role_permissions) (?)
└── N:1 → permission_groups (?)
│         FK: group_id
```

---

## 🔗 علاقات النسخ والتدقيق

### 34. Revision (النسخ) - Polymorphic

```
revisions
├── P:1 → services (revisionable) (?)
├── P:1 → articles (revisionable) (?)
├── P:1 → pages (revisionable) (?)
├── P:1 → products (revisionable) (?)
├── P:1 → projects (revisionable) (?)
├── P:1 → events (revisionable) (?)
├── P:1 → static_blocks (revisionable) (?)
└── N:1 → users (created_by) (!)
```

### 35. Activity Log (سجل النشاط)

```
activity_logs
├── P:1 → [any entity] (subject) (?)
├── P:1 → [any entity] (causer) (?)
└── N:1 → users (performed_by) (?)
```

---

## 🔗 علاقات الإشعارات

### 36. Notification (الإشعارات)

```
notifications
├── N:1 → users (recipient) (!)
│         FK: user_id
├── P:1 → [any entity] (notifiable) (?)
└── N:1 → notification_templates (?)
│         FK: template_id
```

### 37. Webhook (نقاط الـ Webhook)

```
webhooks
├── 1:N → webhook_logs (?)
└── N:1 → users (created_by) (!)
```

---

## 🔗 علاقات SEO

### 38. SEO Meta - Polymorphic

```
seo_metas
├── P:1 → services (seoable) (?)
├── P:1 → articles (seoable) (?)
├── P:1 → pages (seoable) (?)
├── P:1 → products (seoable) (?)
├── P:1 → projects (seoable) (?)
├── P:1 → events (seoable) (?)
├── P:1 → taxonomies (seoable) (?)
└── N:1 → media (og_image) (?)
```

---

## 📊 جداول العلاقات الوسيطة (Pivot Tables)

### 1. content_taxonomies (Polymorphic Many-to-Many)
```sql
- id (PK)
- taggable_type (string) -- 'service', 'article', 'product'...
- taggable_id (uuid)
- taxonomy_id (FK → taxonomies.id)
- order (integer)
- created_at
```

### 2. content_media (Polymorphic Many-to-Many)
```sql
- id (PK)
- mediable_type (string)
- mediable_id (uuid)
- media_id (FK → media.id)
- collection (string) -- 'featured', 'gallery', 'documents'
- order (integer)
- meta (json)
- created_at
```

### 3. content_related (Polymorphic Self Many-to-Many)
```sql
- id (PK)
- source_type (string)
- source_id (uuid)
- related_type (string)
- related_id (uuid)
- relation_type (string) -- 'similar', 'recommended'
- order (integer)
- created_at
```

### 4. role_permissions
```sql
- role_id (FK → roles.id)
- permission_id (FK → permissions.id)
- PRIMARY KEY (role_id, permission_id)
```

### 5. user_roles
```sql
- user_id (FK → users.id)
- role_id (FK → roles.id)
- PRIMARY KEY (user_id, role_id)
```

### 6. plan_coupons
```sql
- plan_id (FK → pricing_plans.id)
- coupon_id (FK → coupons.id)
- PRIMARY KEY (plan_id, coupon_id)
```

### 7. page_blocks
```sql
- id (PK)
- page_id (FK → pages.id)
- block_id (FK → static_blocks.id)
- position (string) -- 'before_content', 'after_content'
- order (integer)
- created_at
```

### 8. event_session_speakers
```sql
- session_id (FK → event_sessions.id)
- speaker_id (FK → event_speakers.id)
- role (string) -- 'main', 'moderator', 'panelist'
- order (integer)
- PRIMARY KEY (session_id, speaker_id)
```

### 9. project_testimonials
```sql
- project_id (FK → projects.id)
- testimonial_id (FK → testimonials.id)
- is_primary (boolean)
- order (integer)
- PRIMARY KEY (project_id, testimonial_id)
```

---

## 📈 مخطط العلاقات المبسط

```
                                    ┌─────────────┐
                                    │    users    │
                                    └──────┬──────┘
                                           │
              ┌────────────────────────────┼────────────────────────────┐
              │                            │                            │
              ▼                            ▼                            ▼
       ┌──────────┐                 ┌──────────┐                 ┌──────────┐
       │  roles   │◄───────────────►│user_roles│◄───────────────►│permissions│
       └──────────┘                 └──────────┘                 └──────────┘
              
                                    ┌─────────────┐
                                    │   content   │
                                    │  (services, │
                                    │  articles,  │
                                    │  products,  │
                                    │   pages)    │
                                    └──────┬──────┘
                                           │
         ┌─────────────────────────────────┼─────────────────────────────────┐
         │                                 │                                 │
         ▼                                 ▼                                 ▼
  ┌──────────────┐                  ┌──────────┐                     ┌───────────┐
  │ translations │                  │taxonomies│                     │   media   │
  │   (locale)   │                  └────┬─────┘                     └─────┬─────┘
  └──────────────┘                       │                                 │
                                         ▼                                 ▼
                                  ┌──────────────┐                  ┌──────────────┐
                                  │   content_   │                  │   content_   │
                                  │  taxonomies  │                  │    media     │
                                  └──────────────┘                  └──────────────┘
```

---

**نهاية تحليل العلاقات**
