# خطة تنفيذ الكود التفصيلية - CMS Laravel 12
## Implementation Roadmap (0% → 100%)

---

**المشروع:** نظام إدارة المحتوى (CMS) - Laravel 12  
**الإصدار:** 1.0  
**التاريخ:** ديسمبر 2024

---

# نظرة عامة على خطة التنفيذ

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        خريطة التنفيذ العملي                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 1: Foundation (0%-15%)                                         │  │
│  │ إعداد المشروع، البنية التحتية، نظام Modules                          │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                    ▼                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 2: Core Modules (15%-35%)                                      │  │
│  │ Auth, Users, Media, Localization, Currency                           │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                    ▼                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 3: Content System (35%-55%)                                    │  │
│  │ Content, Taxonomy, Menu, Search                                      │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                    ▼                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 4: Extended Features (55%-75%)                                 │  │
│  │ Forms, Comments, Notifications, Analytics                            │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                    ▼                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 5: Admin & API (75%-90%)                                       │  │
│  │ Admin Panel, API Layer, Themes                                       │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                    ▼                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Phase 6: Polish & Deploy (90%-100%)                                  │  │
│  │ Testing, Optimization, Documentation, Deployment                     │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

# ملخص المراحل

| Phase | الاسم | النسبة | المدة | Sprints |
|-------|-------|--------|-------|---------|
| **1** | Foundation | 0% - 15% | أسبوعين | Sprint 1 |
| **2** | Core Modules | 15% - 35% | أسبوعين | Sprint 2-3 |
| **3** | Content System | 35% - 55% | أسبوعين | Sprint 4-5 |
| **4** | Extended Features | 55% - 75% | أسبوعين | Sprint 6-7 |
| **5** | Admin & API | 75% - 90% | أسبوعين | Sprint 8-9 |
| **6** | Polish & Deploy | 90% - 100% | أسبوع | Sprint 10 |

**المدة الإجمالية:** 10-11 أسبوع (Sprint = أسبوع واحد)

---

# Phase 1: Foundation (0% - 15%)

## Sprint 1: إعداد المشروع والبنية الأساسية

### الأسبوع 1 - اليوم 1-2: إعداد المشروع

| # | المهمة | الملفات/المجلدات | الأولوية |
|---|--------|------------------|----------|
| 1.1 | إنشاء مشروع Laravel 12 جديد | `/` | 🔴 Critical |
| 1.2 | إعداد Git repository | `.gitignore`, `README.md` | 🔴 Critical |
| 1.3 | إعداد Docker environment | `docker/`, `docker-compose.yml` | 🔴 Critical |
| 1.4 | تكوين قاعدة البيانات PostgreSQL | `.env`, `config/database.php` | 🔴 Critical |
| 1.5 | تكوين Redis للـ Cache/Queue | `config/cache.php`, `config/queue.php` | 🟡 High |

**الأوامر:**
```bash
# إنشاء المشروع
composer create-project laravel/laravel cms
cd cms

# إعداد Git
git init
git add .
git commit -m "Initial Laravel 12 setup"

# Docker
docker-compose up -d
```

**التحقق:**
- [ ] المشروع يعمل على `localhost:8080`
- [ ] قاعدة البيانات متصلة
- [ ] Redis يعمل

---

### الأسبوع 1 - اليوم 3-4: نظام Modules

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 1.6 | إنشاء هيكل مجلد modules/ | `modules/` | 🔴 Critical |
| 1.7 | إنشاء ModuleServiceProvider | `app/Providers/ModuleServiceProvider.php` | 🔴 Critical |
| 1.8 | إنشاء ModuleLoader Service | `app/Services/ModuleLoader.php` | 🔴 Critical |
| 1.9 | إنشاء FeatureManager Service | `app/Services/FeatureManager.php` | 🔴 Critical |
| 1.10 | تسجيل في bootstrap | `bootstrap/providers.php` | 🔴 Critical |

**الملفات المطلوبة:**

```
app/
├── Providers/
│   └── ModuleServiceProvider.php
├── Services/
│   ├── ModuleLoader.php
│   └── FeatureManager.php
└── Contracts/
    └── ModuleContract.php

modules/
└── .gitkeep

config/
├── modules.php
└── features.php

bootstrap/
└── modules.php
```

**التحقق:**
- [ ] `php artisan module:list` يعمل
- [ ] الـ Modules يتم تحميلها تلقائياً

---

### الأسبوع 1 - اليوم 5: Module Scaffolding

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 1.11 | إنشاء أمر module:make | `app/Console/Commands/ModuleMake.php` | 🟡 High |
| 1.12 | إنشاء Stubs للـ Module | `stubs/module/` | 🟡 High |
| 1.13 | إنشاء أمر module:migrate | `app/Console/Commands/ModuleMigrate.php` | 🟡 High |

**Stubs المطلوبة:**
```
stubs/module/
├── service-provider.stub
├── config.stub
├── model.stub
├── controller.stub
├── request.stub
├── resource.stub
├── migration.stub
├── factory.stub
├── seeder.stub
├── routes-api.stub
├── routes-web.stub
└── module-json.stub
```

**التحقق:**
```bash
php artisan module:make TestModule --with-all
# يجب أن ينشئ modules/TestModule/ بكل الملفات
```

---

### الأسبوع 2 - اليوم 1-2: Core Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 1.14 | إنشاء Core Module | `modules/Core/` | 🔴 Critical |
| 1.15 | Base Model class | `modules/Core/Domain/Models/BaseModel.php` | 🔴 Critical |
| 1.16 | Base Controller | `modules/Core/Http/Controllers/BaseController.php` | 🔴 Critical |
| 1.17 | Base Repository | `modules/Core/Repositories/BaseRepository.php` | 🔴 Critical |
| 1.18 | Shared Traits | `modules/Core/Traits/` | 🔴 Critical |

**Traits المطلوبة:**
```
modules/Core/Traits/
├── HasUuid.php
├── HasTranslations.php
├── HasRevisions.php
├── HasMedia.php
├── HasSlug.php
├── HasStatus.php
├── HasOrdering.php
├── Filterable.php
└── Searchable.php
```

---

### الأسبوع 2 - اليوم 3-4: CI/CD و Code Quality

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 1.19 | إعداد PHPStan | `phpstan.neon` | 🟡 High |
| 1.20 | إعداد PHP CS Fixer | `.php-cs-fixer.php` | 🟡 High |
| 1.21 | إعداد GitHub Actions | `.github/workflows/ci.yml` | 🟡 High |
| 1.22 | إعداد PHPUnit | `phpunit.xml` | 🟡 High |
| 1.23 | إنشاء Test Helpers | `tests/TestCase.php` | 🟡 High |

---

### الأسبوع 2 - اليوم 5: Profile System

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 1.24 | إنشاء ProfileLoader | `app/Services/ProfileLoader.php` | 🟡 High |
| 1.25 | إنشاء أمر profile:apply | `app/Console/Commands/ProfileApply.php` | 🟡 High |
| 1.26 | إنشاء Default Profile | `config/profiles/default.yaml` | 🟡 High |

---

## ✅ مخرجات Phase 1 (Checkpoint 15%)

```
cms/
├── app/
│   ├── Console/Commands/
│   │   ├── ModuleMake.php
│   │   ├── ModuleMigrate.php
│   │   └── ProfileApply.php
│   ├── Contracts/
│   │   └── ModuleContract.php
│   ├── Providers/
│   │   └── ModuleServiceProvider.php
│   └── Services/
│       ├── ModuleLoader.php
│       ├── FeatureManager.php
│       └── ProfileLoader.php
├── modules/
│   └── Core/
│       ├── Domain/Models/BaseModel.php
│       ├── Http/Controllers/BaseController.php
│       ├── Repositories/BaseRepository.php
│       ├── Traits/
│       ├── Providers/CoreServiceProvider.php
│       └── module.json
├── stubs/module/
├── config/
│   ├── modules.php
│   ├── features.php
│   └── profiles/default.yaml
├── docker/
├── .github/workflows/ci.yml
├── phpstan.neon
└── docker-compose.yml
```

**اختبارات التحقق:**
- [ ] `php artisan module:make Test` ✓
- [ ] `php artisan module:list` ✓
- [ ] `php artisan test` ✓
- [ ] CI Pipeline يعمل ✓
- [ ] Docker environment يعمل ✓

---

# Phase 2: Core Modules (15% - 35%)

## Sprint 2: Auth & Users Modules

### الأسبوع 3 - اليوم 1-3: Users Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 2.1 | إنشاء Users Module | `modules/Users/` | 🔴 Critical |
| 2.2 | User Model | `modules/Users/Domain/Models/User.php` | 🔴 Critical |
| 2.3 | UserProfile Model | `modules/Users/Domain/Models/UserProfile.php` | 🔴 Critical |
| 2.4 | Users Migration | `modules/Users/Database/Migrations/` | 🔴 Critical |
| 2.5 | UserService | `modules/Users/Services/UserService.php` | 🔴 Critical |
| 2.6 | UserRepository | `modules/Users/Repositories/UserRepository.php` | 🔴 Critical |
| 2.7 | UserPolicy | `modules/Users/Policies/UserPolicy.php` | 🟡 High |

**Schema - users table:**
```
users
├── id (uuid, PK)
├── email (unique)
├── password
├── status (enum: active, inactive, suspended)
├── email_verified_at
├── remember_token
├── last_login_at
├── created_at
├── updated_at
└── deleted_at
```

**Schema - user_profiles table:**
```
user_profiles
├── id (uuid, PK)
├── user_id (FK)
├── first_name
├── last_name
├── phone
├── avatar_id (FK -> media)
├── locale
├── timezone
├── meta (json)
├── created_at
└── updated_at
```

---

### الأسبوع 3 - اليوم 4-5: Auth Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 2.8 | إنشاء Auth Module | `modules/Auth/` | 🔴 Critical |
| 2.9 | Role Model | `modules/Auth/Domain/Models/Role.php` | 🔴 Critical |
| 2.10 | Permission Model | `modules/Auth/Domain/Models/Permission.php` | 🔴 Critical |
| 2.11 | Auth Migrations | `modules/Auth/Database/Migrations/` | 🔴 Critical |
| 2.12 | AuthService | `modules/Auth/Services/AuthService.php` | 🔴 Critical |
| 2.13 | Login/Register Controllers | `modules/Auth/Http/Controllers/` | 🔴 Critical |
| 2.14 | Auth Middleware | `modules/Auth/Http/Middleware/` | 🔴 Critical |
| 2.15 | HasRoles Trait | `modules/Auth/Traits/HasRoles.php` | 🔴 Critical |

**Schema - roles table:**
```
roles
├── id (uuid, PK)
├── slug (unique)
├── name
├── description
├── is_system (boolean)
├── created_at
└── updated_at
```

**Schema - permissions table:**
```
permissions
├── id (uuid, PK)
├── slug (unique)
├── name
├── module
├── group
├── created_at
└── updated_at
```

---

## Sprint 3: Media & Localization Modules

### الأسبوع 4 - اليوم 1-3: Media Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 2.16 | إنشاء Media Module | `modules/Media/` | 🔴 Critical |
| 2.17 | Media Model | `modules/Media/Domain/Models/Media.php` | 🔴 Critical |
| 2.18 | MediaFolder Model | `modules/Media/Domain/Models/MediaFolder.php` | 🟡 High |
| 2.19 | MediaService (upload, process) | `modules/Media/Services/MediaService.php` | 🔴 Critical |
| 2.20 | ImageProcessor | `modules/Media/Services/ImageProcessor.php` | 🔴 Critical |
| 2.21 | FileValidator | `modules/Media/Services/FileValidator.php` | 🔴 Critical |
| 2.22 | Media API Controllers | `modules/Media/Http/Controllers/Api/` | 🔴 Critical |
| 2.23 | HasMedia Trait (polymorphic) | `modules/Media/Traits/HasMedia.php` | 🔴 Critical |

**Schema - media table:**
```
media
├── id (uuid, PK)
├── folder_id (FK, nullable)
├── user_id (FK)
├── disk
├── path
├── filename
├── original_filename
├── mime_type
├── size
├── dimensions (json: width, height)
├── meta (json)
├── created_at
├── updated_at
└── deleted_at
```

---

### الأسبوع 4 - اليوم 4-5: Localization Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 2.24 | إنشاء Localization Module | `modules/Localization/` | 🔴 Critical |
| 2.25 | Language Model | `modules/Localization/Domain/Models/Language.php` | 🔴 Critical |
| 2.26 | Translation Model | `modules/Localization/Domain/Models/Translation.php` | 🟡 High |
| 2.27 | LocaleResolver Service | `modules/Localization/Services/LocaleResolver.php` | 🔴 Critical |
| 2.28 | TranslationService | `modules/Localization/Services/TranslationService.php` | 🟡 High |
| 2.29 | LocaleMiddleware | `modules/Localization/Http/Middleware/SetLocale.php` | 🔴 Critical |
| 2.30 | HasTranslations Trait | `modules/Localization/Traits/HasTranslations.php` | 🔴 Critical |

**Schema - languages table:**
```
languages
├── id (uuid, PK)
├── code (unique, e.g., 'ar', 'en')
├── name
├── native_name
├── direction (ltr/rtl)
├── is_default
├── is_active
├── flag
├── ordering
├── created_at
└── updated_at
```

---

### الأسبوع 5 - اليوم 1-2: Currency Module

| # | المهمة | الملفات | الأولوية |
|---|--------|---------|----------|
| 2.31 | إنشاء Currency Module | `modules/Currency/` | 🟡 High |
| 2.32 | Currency Model | `modules/Currency/Domain/Models/Currency.php` | 🟡 High |
| 2.33 | ExchangeRate Model | `modules/Currency/Domain/Models/ExchangeRate.php` | 🟡 High |
| 2.34 | CurrencyConverter Service | `modules/Currency/Services/CurrencyConverter.php` | 🟡 High |
| 2.35 | ExchangeRateSync Job | `modules/Currency/Jobs/SyncExchangeRates.php` | 🟢 Medium |

---

## ✅ مخرجات Phase 2 (Checkpoint 35%)

```
modules/
├── Core/           ✓
├── Users/          ✓ NEW
│   ├── Domain/Models/
│   │   ├── User.php
│   │   └── UserProfile.php
│   ├── Services/UserService.php
│   ├── Repositories/UserRepository.php
│   └── Policies/UserPolicy.php
├── Auth/           ✓ NEW
│   ├── Domain/Models/
│   │   ├── Role.php
│   │   └── Permission.php
│   ├── Services/AuthService.php
│   ├── Http/Controllers/
│   ├── Http/Middleware/
│   └── Traits/HasRoles.php
├── Media/          ✓ NEW
│   ├── Domain/Models/
│   │   ├── Media.php
│   │   └── MediaFolder.php
│   ├── Services/
│   │   ├── MediaService.php
│   │   └── ImageProcessor.php
│   └── Traits/HasMedia.php
├── Localization/   ✓ NEW
│   ├── Domain/Models/
│   │   ├── Language.php
│   │   └── Translation.php
│   ├── Services/
│   │   ├── LocaleResolver.php
│   │   └── TranslationService.php
│   └── Traits/HasTranslations.php
└── Currency/       ✓ NEW
    ├── Domain/Models/
    │   ├── Currency.php
    │   └── ExchangeRate.php
    └── Services/CurrencyConverter.php
```

**اختبارات التحقق:**
- [ ] تسجيل مستخدم جديد ✓
- [ ] تسجيل الدخول/الخروج ✓
- [ ] الصلاحيات تعمل ✓
- [ ] رفع ملف وعرضه ✓
- [ ] تبديل اللغة يعمل ✓
- [ ] تحويل العملة يعمل ✓
