# دليل هيكلة CMS احترافي - Laravel 12
## الجزء الأول: المبادئ والمعمارية

---

# 1. مبادئ التصميم عالية المستوى

## 1.1 Modularity (النمطية)
```
✅ كل ميزة = Module مستقل
✅ كل Module له Service Provider خاص
✅ لا dependencies مباشرة بين Modules — فقط عبر Contracts
✅ يمكن تفعيل/تعطيل أي Module دون كسر النظام
```

## 1.2 Separation of Concerns
```
┌─────────────────────────────────────────────────────────────┐
│ Layer                │ Responsibility                       │
├─────────────────────────────────────────────────────────────┤
│ HTTP/API Layer       │ Request/Response handling only       │
│ Application Layer    │ Use cases, orchestration             │
│ Domain Layer         │ Business logic, entities             │
│ Infrastructure Layer │ DB, external services, caching       │
└─────────────────────────────────────────────────────────────┘
```

## 1.3 Contract-Driven Design
```php
// ❌ Bad: Direct dependency
class ArticleService {
    public function __construct(ElasticsearchClient $search) {}
}

// ✅ Good: Contract dependency
class ArticleService {
    public function __construct(SearchEngineContract $search) {}
}
```

## 1.4 Feature Toggles
```php
// config/features.php
return [
    'modules' => [
        'blog' => env('FEATURE_BLOG', true),
        'ecommerce' => env('FEATURE_ECOMMERCE', false),
        'events' => env('FEATURE_EVENTS', false),
    ],
];

// Usage
if (Feature::enabled('blog')) {
    // Register blog routes, views, etc.
}
```

## 1.5 Backward-Compatible Migrations
```
✅ Never drop columns in same release — deprecate first
✅ Add nullable columns or with defaults
✅ Use data migrations for transformations
✅ Always provide rollback scripts
```

## 1.6 API Versioning
```
/api/v1/articles     → Stable, production
/api/v2/articles     → New features, beta
/api/internal/*      → Admin-only, not versioned
```

## 1.7 Multi-Tenancy Considerations
```php
// Option A: Single DB, tenant_id column
// Option B: DB per tenant (recommended for isolation)
// Option C: Schema per tenant (PostgreSQL)

// Laravel 12 approach
class TenantScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        $builder->where('tenant_id', tenant()->id);
    }
}
```

---

# 2. المعمارية المقترحة

## 2.1 Modular Monolith vs Microservices

```
┌─────────────────────────────────────────────────────────────────┐
│                    RECOMMENDED: Modular Monolith                │
├─────────────────────────────────────────────────────────────────┤
│ ✅ Single deployment unit                                       │
│ ✅ Shared database with module boundaries                       │
│ ✅ Internal contracts between modules                           │
│ ✅ Can extract to microservices later if needed                 │
│ ✅ Simpler DevOps, lower operational cost                       │
│ ✅ Easier transactions across modules                           │
├─────────────────────────────────────────────────────────────────┤
│ When to consider Microservices:                                 │
│ • Team > 50 developers                                          │
│ • Modules need independent scaling                              │
│ • Different tech stacks per module                              │
│ • Strict failure isolation required                             │
└─────────────────────────────────────────────────────────────────┘
```

## 2.2 System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              CLIENTS                                     │
│   ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐         │
│   │ Web App  │    │ Admin UI │    │Mobile App│    │ 3rd Party│         │
│   │ (Blade)  │    │ (Inertia)│    │  (API)   │    │(Webhooks)│         │
│   └────┬─────┘    └────┬─────┘    └────┬─────┘    └────┬─────┘         │
└────────┼───────────────┼───────────────┼───────────────┼────────────────┘
         │               │               │               │
         ▼               ▼               ▼               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           API GATEWAY / ROUTING                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │  Web Routes │  │ API v1      │  │ API v2      │  │  Webhooks   │    │
│  │  (Blade)    │  │ (JSON)      │  │ (JSON)      │  │  Handler    │    │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         APPLICATION LAYER                                │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                        MODULES                                    │   │
│  │  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐         │   │
│  │  │  Core  │ │Content │ │ Media  │ │  Auth  │ │  Shop  │  ...    │   │
│  │  └───┬────┘ └───┬────┘ └───┬────┘ └───┬────┘ └───┬────┘         │   │
│  │      │         │         │         │         │                   │   │
│  │      └─────────┴─────────┴────┬────┴─────────┘                   │   │
│  │                               │                                   │   │
│  │                    ┌──────────▼──────────┐                       │   │
│  │                    │   SHARED KERNEL     │                       │   │
│  │                    │  (Contracts, DTOs)  │                       │   │
│  │                    └─────────────────────┘                       │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
         ┌──────────────────────────┼──────────────────────────┐
         ▼                          ▼                          ▼
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│   PERSISTENCE   │      │   ASYNC JOBS    │      │   INTEGRATIONS  │
│  ┌───────────┐  │      │  ┌───────────┐  │      │  ┌───────────┐  │
│  │PostgreSQL │  │      │  │  Redis    │  │      │  │  Search   │  │
│  │  / MySQL  │  │      │  │  Queue    │  │      │  │(Meilisearch)│ │
│  └───────────┘  │      │  └───────────┘  │      │  └───────────┘  │
│  ┌───────────┐  │      │  ┌───────────┐  │      │  ┌───────────┐  │
│  │   Redis   │  │      │  │ Scheduler │  │      │  │  Payment  │  │
│  │   Cache   │  │      │  │  (Cron)   │  │      │  │  Gateway  │  │
│  └───────────┘  │      │  └───────────┘  │      │  └───────────┘  │
└─────────────────┘      └─────────────────┘      └─────────────────┘
```

## 2.3 Laravel 12 Layers Implementation

### HTTP Layer
```php
// app/Http/Controllers/Api/V1/ArticleController.php
final class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleServiceContract $articleService,
    ) {}

    public function index(ArticleIndexRequest $request): ArticleCollection
    {
        $articles = $this->articleService->list(
            ArticleFiltersDTO::fromRequest($request)
        );
        
        return new ArticleCollection($articles);
    }
}
```

### Application Layer (Services)
```php
// modules/Content/Services/ArticleService.php
final class ArticleService implements ArticleServiceContract
{
    public function __construct(
        private readonly ArticleRepositoryContract $repository,
        private readonly EventDispatcher $events,
    ) {}

    public function publish(Article $article): Article
    {
        DB::transaction(function () use ($article) {
            $article->publish();
            $this->repository->save($article);
            $this->events->dispatch(new ArticlePublished($article));
        });
        
        return $article;
    }
}
```

### Domain Layer
```php
// modules/Content/Domain/Article.php
final class Article extends Model
{
    use HasTranslations, HasRevisions, HasMedia, SoftDeletes;

    public function publish(): void
    {
        $this->status = ArticleStatus::Published;
        $this->published_at = now();
    }

    public function canBePublished(): bool
    {
        return $this->status === ArticleStatus::Draft 
            && $this->hasRequiredTranslations();
    }
}
```

### Infrastructure Layer
```php
// modules/Content/Repositories/EloquentArticleRepository.php
final class EloquentArticleRepository implements ArticleRepositoryContract
{
    public function findById(ArticleId $id): ?Article
    {
        return Article::find($id->value);
    }

    public function save(Article $article): void
    {
        $article->save();
    }
}
```

## 2.4 Event-Driven Architecture

```php
// Events flow
┌────────────┐     ┌────────────┐     ┌────────────┐
│   Action   │────▶│   Event    │────▶│  Listeners │
│ (publish)  │     │ Dispatched │     │  (async)   │
└────────────┘     └────────────┘     └────────────┘
                                             │
                   ┌─────────────────────────┼─────────────────────────┐
                   ▼                         ▼                         ▼
            ┌────────────┐           ┌────────────┐           ┌────────────┐
            │   Index    │           │   Send     │           │  Trigger   │
            │  Search    │           │  Webhook   │           │   Cache    │
            └────────────┘           └────────────┘           │ Invalidate │
                                                              └────────────┘
```

```php
// app/Events/ArticlePublished.php
final class ArticlePublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Article $article,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('articles')];
    }
}

// Listeners registered in Module Service Provider
protected $listen = [
    ArticlePublished::class => [
        IndexArticleInSearch::class,      // Queue: search
        InvalidateArticleCache::class,    // Queue: cache
        TriggerArticleWebhooks::class,    // Queue: webhooks
        NotifySubscribers::class,         // Queue: notifications
    ],
];
```

---

# 3. نموذج تقسيم الكود إلى Modules

## 3.1 قائمة Modules المقترحة

```
modules/
├── Core/                 # Foundation (required)
├── Content/              # Articles, Pages, Services
├── Media/                # Files, Images, Videos
├── Users/                # Users, Profiles
├── Auth/                 # Authentication, Authorization
├── Taxonomy/             # Categories, Tags
├── Menu/                 # Navigation
├── Localization/         # Languages, Translations
├── Currency/             # Currencies, Exchange Rates
├── Search/               # Search indexing
├── Forms/                # Dynamic forms
├── Comments/             # User comments
├── Ecommerce/            # Products, Cart, Checkout
├── Pricing/              # Plans, Subscriptions
├── Events/               # Events, Registrations
├── Analytics/            # Page views, Reports
├── Notifications/        # Email, Push, SMS
├── Integrations/         # Third-party APIs
└── Themes/               # Frontend themes
```

## 3.2 تفاصيل كل Module

### Core Module (Required)
```yaml
name: Core
description: Foundation services, base classes, shared utilities
dependencies: []
provides:
  - BaseModel, BaseController, BaseService
  - Feature toggle system
  - Module loader
  - Shared DTOs and Value Objects
  - Event dispatcher
  - Cache manager
  - Configuration manager

contracts:
  - CacheContract
  - EventDispatcherContract
  - ConfigurationContract
  - FeatureManagerContract
```

### Content Module
```yaml
name: Content
description: Content types management (Articles, Pages, Services, Projects)
dependencies: [Core, Media, Taxonomy, Localization]
provides:
  - Content CRUD operations
  - Publishing workflow
  - Revisions management
  - SEO metadata

contracts:
  - ContentRepositoryContract
  - ContentServiceContract
  - PublishingWorkflowContract
  - RevisionManagerContract

events:
  - ContentCreated
  - ContentUpdated
  - ContentPublished
  - ContentArchived
  - ContentDeleted
```

### Media Module
```yaml
name: Media
description: File uploads, image processing, variants
dependencies: [Core]
provides:
  - File upload handling
  - Image optimization
  - Variant generation
  - CDN integration
  - Folder management

contracts:
  - MediaStorageContract
  - ImageProcessorContract
  - MediaRepositoryContract

config:
  - max_upload_size
  - allowed_mime_types
  - variant_sizes
  - storage_disk
```

### Auth Module
```yaml
name: Auth
description: Authentication and authorization
dependencies: [Core, Users]
provides:
  - Login/Logout
  - Password reset
  - Two-factor authentication
  - API tokens
  - RBAC (Roles, Permissions)
  - Policies

contracts:
  - AuthenticationContract
  - AuthorizationContract
  - TokenManagerContract
```

### Localization Module
```yaml
name: Localization
description: Multi-language support
dependencies: [Core]
provides:
  - Language management
  - Translation keys
  - Content translation workflow
  - Fallback chains
  - RTL support

contracts:
  - TranslatorContract
  - LanguageRepositoryContract
  - LocaleResolverContract
```

### Currency Module
```yaml
name: Currency
description: Multi-currency and exchange rates
dependencies: [Core]
provides:
  - Currency management
  - Exchange rates sync
  - Price conversion
  - Formatting per locale

contracts:
  - CurrencyConverterContract
  - ExchangeRateProviderContract
  - PriceFormatterContract

scheduled_jobs:
  - SyncExchangeRates (hourly)
```

### Ecommerce Module
```yaml
name: Ecommerce
description: Products, variants, inventory, cart
dependencies: [Core, Media, Taxonomy, Currency, Pricing]
provides:
  - Product management
  - Variant management
  - Inventory tracking
  - Cart management
  - Checkout flow

contracts:
  - ProductRepositoryContract
  - CartContract
  - InventoryContract
  - CheckoutContract

events:
  - ProductPublished
  - StockUpdated
  - CartUpdated
  - OrderPlaced
```

## 3.3 هيكل Module في Laravel 12

```
modules/Content/
├── Config/
│   └── content.php
├── Console/
│   └── Commands/
│       └── PublishScheduledContent.php
├── Contracts/
│   ├── ContentRepositoryContract.php
│   ├── ContentServiceContract.php
│   └── PublishingWorkflowContract.php
├── Database/
│   ├── Factories/
│   │   └── ArticleFactory.php
│   ├── Migrations/
│   │   ├── 2024_01_01_000001_create_articles_table.php
│   │   └── 2024_01_01_000002_create_article_translations_table.php
│   └── Seeders/
│       └── ContentSeeder.php
├── Domain/
│   ├── Models/
│   │   ├── Article.php
│   │   ├── Page.php
│   │   └── Service.php
│   ├── DTOs/
│   │   ├── ArticleData.php
│   │   └── ArticleFiltersDTO.php
│   ├── Enums/
│   │   └── ContentStatus.php
│   └── ValueObjects/
│       └── Slug.php
├── Events/
│   ├── ArticlePublished.php
│   └── ArticleCreated.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── ArticleController.php
│   │   └── Admin/
│   │       └── ArticleController.php
│   ├── Requests/
│   │   ├── StoreArticleRequest.php
│   │   └── UpdateArticleRequest.php
│   ├── Resources/
│   │   ├── ArticleResource.php
│   │   └── ArticleCollection.php
│   └── Middleware/
│       └── EnsureContentAccess.php
├── Jobs/
│   ├── PublishArticle.php
│   └── IndexArticle.php
├── Listeners/
│   ├── IndexArticleInSearch.php
│   └── InvalidateArticleCache.php
├── Policies/
│   └── ArticlePolicy.php
├── Providers/
│   ├── ContentServiceProvider.php
│   ├── ContentEventServiceProvider.php
│   └── ContentRouteServiceProvider.php
├── Repositories/
│   └── EloquentArticleRepository.php
├── Routes/
│   ├── api.php
│   ├── web.php
│   └── admin.php
├── Services/
│   ├── ArticleService.php
│   └── PublishingWorkflow.php
├── Tests/
│   ├── Feature/
│   │   └── ArticleApiTest.php
│   └── Unit/
│       └── ArticleServiceTest.php
├── Views/
│   └── admin/
│       └── articles/
│           ├── index.blade.php
│           └── edit.blade.php
├── composer.json
└── module.json
```

## 3.4 Module Manifest (module.json)

```json
{
    "name": "Content",
    "alias": "content",
    "description": "Content management module",
    "version": "1.0.0",
    "keywords": ["articles", "pages", "cms"],
    "priority": 10,
    "providers": [
        "Modules\\Content\\Providers\\ContentServiceProvider"
    ],
    "aliases": {},
    "files": [],
    "requires": {
        "core": "^1.0",
        "media": "^1.0",
        "taxonomy": "^1.0"
    },
    "features": {
        "articles": true,
        "pages": true,
        "services": true,
        "revisions": true,
        "workflow": true
    },
    "permissions": [
        "content.view",
        "content.create",
        "content.update",
        "content.delete",
        "content.publish"
    ]
}
```

## 3.5 Module Service Provider (Laravel 12)

```php
<?php

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Content\Contracts\ContentRepositoryContract;
use Modules\Content\Contracts\ContentServiceContract;
use Modules\Content\Repositories\EloquentArticleRepository;
use Modules\Content\Services\ArticleService;

final class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register config
        $this->mergeConfigFrom(__DIR__.'/../Config/content.php', 'content');

        // Bind contracts
        $this->app->bind(ContentRepositoryContract::class, EloquentArticleRepository::class);
        $this->app->bind(ContentServiceContract::class, ArticleService::class);
    }

    public function boot(): void
    {
        // Check if module is enabled
        if (!$this->app['features']->enabled('content')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Views', 'content');
        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'content');

        $this->registerRoutes();
        $this->registerCommands();
        $this->registerPolicies();
    }

    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../Routes/api.php');

        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin')
            ->group(__DIR__.'/../Routes/admin.php');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Content\Console\Commands\PublishScheduledContent::class,
            ]);
        }
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
    }
}
```

---

**نهاية الجزء الأول - يتبع الجزء الثاني: نظام Modules وFeature Toggles**
