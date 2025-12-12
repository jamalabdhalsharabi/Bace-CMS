# تحليل الكيانات (Entities Analysis)
## نظام إدارة المحتوى CMS - النموذج البنيوي

---

## 📋 نظرة عامة

هذا المستند يحدد جميع الكيانات المطلوبة لنظام CMS احترافي متعدد القطاعات.

---

## 🏗️ تصنيف الكيانات

### المجموعة 1: كيانات المحتوى الأساسية (Core Content)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 1 | Service | `services` | الخدمات المقدمة |
| 2 | Service Translation | `service_translations` | ترجمات الخدمات |
| 3 | Page | `pages` | الصفحات الثابتة |
| 4 | Page Translation | `page_translations` | ترجمات الصفحات |
| 5 | Article | `articles` | المقالات والأخبار |
| 6 | Article Translation | `article_translations` | ترجمات المقالات |
| 7 | Product | `products` | المنتجات |
| 8 | Product Translation | `product_translations` | ترجمات المنتجات |
| 9 | Product Variant | `product_variants` | متغيرات المنتجات |
| 10 | Project | `projects` | المشاريع والأعمال |
| 11 | Project Translation | `project_translations` | ترجمات المشاريع |
| 12 | Event | `events` | الفعاليات |
| 13 | Event Translation | `event_translations` | ترجمات الفعاليات |
| 14 | Testimonial | `testimonials` | التوصيات |
| 15 | Testimonial Translation | `testimonial_translations` | ترجمات التوصيات |
| 16 | Static Block | `static_blocks` | الأقسام الثابتة |
| 17 | Static Block Translation | `static_block_translations` | ترجمات الأقسام |

### المجموعة 2: كيانات التصنيف (Taxonomy)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 18 | Taxonomy Type | `taxonomy_types` | أنواع التصنيفات (category, tag, industry...) |
| 19 | Taxonomy | `taxonomies` | التصنيفات والوسوم |
| 20 | Taxonomy Translation | `taxonomy_translations` | ترجمات التصنيفات |

### المجموعة 3: كيانات الوسائط (Media)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 21 | Media | `media` | ملفات الوسائط |
| 22 | Media Translation | `media_translations` | ترجمات الوسائط (alt, title) |
| 23 | Media Folder | `media_folders` | مجلدات الوسائط |
| 24 | Media Variant | `media_variants` | متغيرات الوسائط (thumbnails) |

### المجموعة 4: كيانات القوائم (Navigation)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 25 | Menu | `menus` | القوائم |
| 26 | Menu Translation | `menu_translations` | ترجمات القوائم |
| 27 | Menu Item | `menu_items` | عناصر القوائم |
| 28 | Menu Item Translation | `menu_item_translations` | ترجمات العناصر |
| 29 | Menu Location | `menu_locations` | مواقع القوائم |

### المجموعة 5: كيانات النماذج (Forms)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 30 | Form | `forms` | تعريفات النماذج |
| 31 | Form Field | `form_fields` | حقول النماذج |
| 32 | Form Submission | `form_submissions` | الإرسالات |
| 33 | Submission Field Value | `submission_field_values` | قيم الحقول |
| 34 | Submission Attachment | `submission_attachments` | مرفقات الإرسال |

### المجموعة 6: كيانات التعليقات (Comments)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 35 | Comment | `comments` | التعليقات (Polymorphic) |
| 36 | Comment Vote | `comment_votes` | تصويتات التعليقات |
| 37 | Comment Report | `comment_reports` | بلاغات التعليقات |

### المجموعة 7: كيانات الفعاليات (Events Extended)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 38 | Event Ticket Type | `event_ticket_types` | أنواع التذاكر |
| 39 | Event Session | `event_sessions` | جلسات الفعالية |
| 40 | Event Session Translation | `event_session_translations` | ترجمات الجلسات |
| 41 | Event Speaker | `event_speakers` | المتحدثون |
| 42 | Event Registration | `event_registrations` | التسجيلات |
| 43 | Event Ticket | `event_tickets` | التذاكر المباعة |
| 44 | Event Check-in | `event_checkins` | تسجيل الحضور |

### المجموعة 8: كيانات التسعير (Pricing)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 45 | Pricing Plan | `pricing_plans` | خطط التسعير |
| 46 | Pricing Plan Translation | `pricing_plan_translations` | ترجمات الخطط |
| 47 | Plan Price | `plan_prices` | أسعار الخطط بالعملات |
| 48 | Plan Feature | `plan_features` | ميزات الخطط |
| 49 | Plan Feature Translation | `plan_feature_translations` | ترجمات الميزات |
| 50 | Plan Limit | `plan_limits` | حدود الاستخدام |
| 51 | Subscription | `subscriptions` | الاشتراكات |
| 52 | Subscription Usage | `subscription_usages` | استخدام الاشتراكات |
| 53 | Coupon | `coupons` | الكوبونات |
| 54 | Coupon Usage | `coupon_usages` | استخدامات الكوبونات |

### المجموعة 9: كيانات المنتجات الموسعة (Products Extended)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 55 | Product Price | `product_prices` | أسعار المنتجات بالعملات |
| 56 | Product Inventory | `product_inventories` | مخزون المنتجات |
| 57 | Inventory Movement | `inventory_movements` | حركات المخزون |
| 58 | Stock Reservation | `stock_reservations` | حجوزات المخزون |
| 59 | Product Attribute | `product_attributes` | سمات المنتجات |
| 60 | Attribute Value | `attribute_values` | قيم السمات |
| 61 | Variant Attribute Value | `variant_attribute_values` | قيم سمات المتغيرات |

### المجموعة 10: كيانات العملات والصرف (Currency)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 62 | Currency | `currencies` | العملات |
| 63 | Currency Translation | `currency_translations` | ترجمات العملات |
| 64 | Exchange Rate | `exchange_rates` | أسعار الصرف |
| 65 | Exchange Rate History | `exchange_rate_history` | تاريخ أسعار الصرف |

### المجموعة 11: كيانات اللغات (Localization)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 66 | Language | `languages` | اللغات |
| 67 | Translation Key | `translation_keys` | مفاتيح الترجمة |
| 68 | Translation Value | `translation_values` | قيم الترجمة |
| 69 | Translation Group | `translation_groups` | مجموعات الترجمة |

### المجموعة 12: كيانات المستخدمين والصلاحيات (Auth)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 70 | User | `users` | المستخدمون |
| 71 | Role | `roles` | الأدوار |
| 72 | Permission | `permissions` | الصلاحيات |
| 73 | User Profile | `user_profiles` | بيانات المستخدمين الإضافية |
| 74 | User Session | `user_sessions` | جلسات المستخدمين |
| 75 | Password Reset | `password_resets` | طلبات إعادة تعيين كلمة المرور |
| 76 | User Ban | `user_bans` | حظر المستخدمين |

### المجموعة 13: كيانات النسخ والتدقيق (Versioning & Audit)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 77 | Revision | `revisions` | النسخ السابقة (Polymorphic) |
| 78 | Activity Log | `activity_logs` | سجل النشاطات |
| 79 | Audit Trail | `audit_trails` | سجل التدقيق |

### المجموعة 14: كيانات الإعدادات (Settings)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 80 | Setting | `settings` | إعدادات النظام |
| 81 | Setting Group | `setting_groups` | مجموعات الإعدادات |
| 82 | User Setting | `user_settings` | إعدادات المستخدم |

### المجموعة 15: كيانات الإشعارات (Notifications)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 83 | Notification | `notifications` | الإشعارات |
| 84 | Notification Template | `notification_templates` | قوالب الإشعارات |
| 85 | Email Log | `email_logs` | سجل الإيميلات |
| 86 | Webhook | `webhooks` | نقاط الـ Webhook |
| 87 | Webhook Log | `webhook_logs` | سجل الـ Webhooks |

### المجموعة 16: كيانات SEO والتحليلات (SEO & Analytics)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 88 | SEO Meta | `seo_metas` | بيانات SEO (Polymorphic) |
| 89 | Redirect | `redirects` | التحويلات |
| 90 | Page View | `page_views` | مشاهدات الصفحات |
| 91 | Search Log | `search_logs` | سجل البحث |

### المجموعة 17: كيانات الجدولة والمهام (Jobs & Scheduling)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 92 | Scheduled Task | `scheduled_tasks` | المهام المجدولة |
| 93 | Job Batch | `job_batches` | دفعات المهام |
| 94 | Failed Job | `failed_jobs` | المهام الفاشلة |

### المجموعة 18: جداول العلاقات الوسيطة (Pivot Tables)

| # | الكيان | الجدول | الوصف |
|---|--------|--------|-------|
| 95 | Content Taxonomy | `content_taxonomies` | ربط المحتوى بالتصنيفات |
| 96 | Content Media | `content_media` | ربط المحتوى بالوسائط |
| 97 | Content Related | `content_related` | المحتوى المرتبط |
| 98 | Role Permission | `role_permissions` | ربط الأدوار بالصلاحيات |
| 99 | User Role | `user_roles` | ربط المستخدمين بالأدوار |
| 100 | Plan Coupon | `plan_coupons` | ربط الخطط بالكوبونات |

---

## 📊 إحصائيات الكيانات

| المجموعة | عدد الكيانات |
|----------|--------------|
| Core Content | 17 |
| Taxonomy | 3 |
| Media | 4 |
| Navigation | 5 |
| Forms | 5 |
| Comments | 3 |
| Events Extended | 7 |
| Pricing | 10 |
| Products Extended | 7 |
| Currency | 4 |
| Localization | 4 |
| Auth | 7 |
| Versioning & Audit | 3 |
| Settings | 3 |
| Notifications | 5 |
| SEO & Analytics | 4 |
| Jobs & Scheduling | 3 |
| Pivot Tables | 6 |
| **المجموع** | **100 كيان** |

---

## 🔑 ملاحظات التصميم

### 1. نمط الترجمة (Translation Pattern)
```
Main Table (services)
    └── Translation Table (service_translations)
        - service_id (FK)
        - locale (string)
        - translatable fields...
```

### 2. نمط Polymorphic
يُستخدم للكيانات المشتركة:
- `comments` → commentable_type, commentable_id
- `revisions` → revisionable_type, revisionable_id
- `content_media` → mediable_type, mediable_id
- `content_taxonomies` → taggable_type, taggable_id
- `seo_metas` → seoable_type, seoable_id

### 3. نمط Soft Deletes
جميع الكيانات الأساسية تدعم:
```
- deleted_at (timestamp, nullable)
- deleted_by (FK → users.id, nullable)
```

### 4. نمط Timestamps
```
- created_at (timestamp)
- updated_at (timestamp)
- created_by (FK → users.id)
- updated_by (FK → users.id)
```

---

**نهاية تحليل الكيانات**
