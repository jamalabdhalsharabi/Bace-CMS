# دليل هيكلة CMS احترافي - Laravel 12
## الجزء الثالث: Database وAPI وExtensibility

---

# 7. Database & Migrations Strategy

## 7.1 Module Migration Structure

```
modules/Content/Database/Migrations/
├── 2024_01_01_000001_create_articles_table.php
├── 2024_01_01_000002_create_article_translations_table.php
├── 2024_01_01_000003_add_seo_fields_to_articles.php
├── 2024_06_01_000001_add_reading_time_to_articles.php    # Feature addition
└── data/
    ├── 2024_01_01_000001_seed_default_article_types.php  # Data migration
    └── 2024_06_01_000001_backfill_reading_time.php       # Backfill
```

## 7.2 Safe Migration Patterns

### Pattern 1: Adding Nullable Column
```php
<?php
// ✅ SAFE: Adding nullable column

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->integer('reading_time')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('reading_time');
        });
    }
};
```

### Pattern 2: Adding Column with Default
```php
<?php
// ✅ SAFE: Adding column with default (PostgreSQL/MySQL 8+)

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('is_featured');
        });
    }
};
```

### Pattern 3: Renaming Column (Two-Step)
```php
<?php
// Step 1: Add new column, copy data
// 2024_06_01_000001_add_new_column.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('summary', 500)->nullable()->after('excerpt');
        });

        // Copy data
        DB::statement('UPDATE articles SET summary = excerpt');
    }
};

// Step 2: After deployment confirmed, remove old column
// 2024_07_01_000001_remove_old_excerpt_column.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('excerpt');
        });
    }
};
```

### Pattern 4: Zero-Downtime Index Creation
```php
<?php
// ✅ SAFE: Concurrent index creation (PostgreSQL)

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL concurrent index
        DB::statement('CREATE INDEX CONCURRENTLY idx_articles_published ON articles (published_at) WHERE status = \'published\'');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_articles_published');
    }
};
```

### Pattern 5: Data Migration with Batching
```php
<?php
// Data migration: Backfill reading_time

return new class extends Migration
{
    public function up(): void
    {
        Article::query()
            ->whereNull('reading_time')
            ->chunkById(1000, function ($articles) {
                foreach ($articles as $article) {
                    $wordCount = str_word_count(strip_tags($article->content));
                    $readingTime = max(1, ceil($wordCount / 200));
                    
                    $article->updateQuietly(['reading_time' => $readingTime]);
                }
            });
    }
};
```

## 7.3 Schema Change Strategies

### Changing Column Type
```php
<?php
// CAREFUL: Changing column type

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new column
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_new', 15, 4)->nullable();
        });

        // Step 2: Copy and transform data
        DB::statement('UPDATE products SET price_new = CAST(price AS DECIMAL(15,4))');

        // Step 3: Swap columns
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->renameColumn('price_new', 'price');
        });
    }
};
```

### Removing Unused Column
```php
<?php
// Step 1: Mark as deprecated (in code)
// Step 2: Stop writing to column
// Step 3: After confirmation period, remove

return new class extends Migration
{
    public function up(): void
    {
        // Safety check: Ensure column is not being used
        $recentWrites = DB::table('articles')
            ->where('updated_at', '>=', now()->subDays(7))
            ->whereNotNull('deprecated_field')
            ->count();

        if ($recentWrites > 0) {
            throw new \Exception("Column still in use. Aborting migration.");
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('deprecated_field');
        });
    }
};
```

## 7.4 Rollback & Compensation Scripts

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class MigrationRollbackSafe extends Command
{
    protected $signature = 'migrate:rollback-safe 
                            {--step=1 : Steps to rollback}
                            {--dry-run : Show what would be rolled back}';

    public function handle(): int
    {
        $migrations = $this->getMigrationsToRollback();

        if ($this->option('dry-run')) {
            $this->info("Would rollback:");
            foreach ($migrations as $m) {
                $this->line("  - {$m}");
            }
            return Command::SUCCESS;
        }

        // Check for data loss
        foreach ($migrations as $migration) {
            if ($this->hasDataLossRisk($migration)) {
                if (!$this->confirm("Migration {$migration} may cause data loss. Continue?")) {
                    return Command::FAILURE;
                }
            }
        }

        // Run compensation scripts first
        foreach ($migrations as $migration) {
            $this->runCompensation($migration);
        }

        // Then rollback
        $this->call('migrate:rollback', ['--step' => $this->option('step')]);

        return Command::SUCCESS;
    }

    protected function hasDataLossRisk(string $migration): bool
    {
        // Check if migration drops tables or columns
        $content = file_get_contents($this->getMigrationPath($migration));
        return str_contains($content, 'dropColumn') 
            || str_contains($content, 'dropIfExists');
    }

    protected function runCompensation(string $migration): void
    {
        $compensationClass = $this->getCompensationClass($migration);
        if (class_exists($compensationClass)) {
            (new $compensationClass)->handle();
        }
    }
}
```

## 7.5 Module Migration Commands

```bash
# Run all pending migrations
php artisan migrate

# Run migrations for specific module
php artisan module:migrate Content

# Rollback specific module
php artisan module:migrate-rollback Content --step=1

# Fresh migration for module (development only)
php artisan module:migrate-fresh Content --seed

# Check migration status per module
php artisan module:migrate-status

# Generate migration for module
php artisan module:make-migration create_products_table Ecommerce

# Run data migrations only
php artisan migrate --path=modules/Content/Database/Migrations/data
```

---

# 8. API & Contracts

## 8.1 API Versioning Strategy

```
routes/
├── api/
│   ├── v1/
│   │   ├── articles.php
│   │   ├── products.php
│   │   └── auth.php
│   └── v2/
│       ├── articles.php
│       └── products.php
└── api.php
```

```php
<?php
// routes/api.php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(base_path('routes/api/v1/articles.php'));
Route::prefix('v1')->group(base_path('routes/api/v1/products.php'));

Route::prefix('v2')->group(base_path('routes/api/v2/articles.php'));
```

## 8.2 API Resource Layer

```php
<?php
// modules/Content/Http/Resources/V1/ArticleResource.php

namespace Modules\Content\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'article',
            'attributes' => [
                'title' => $this->title,
                'slug' => $this->slug,
                'excerpt' => $this->excerpt,
                'content' => $this->when($request->routeIs('*.show'), $this->content),
                'status' => $this->status,
                'is_featured' => $this->is_featured,
                'reading_time' => $this->reading_time,
                'view_count' => $this->view_count,
                'published_at' => $this->published_at?->toIso8601String(),
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => [
                'author' => new UserResource($this->whenLoaded('author')),
                'featured_image' => new MediaResource($this->whenLoaded('featuredImage')),
                'categories' => TaxonomyResource::collection($this->whenLoaded('categories')),
                'tags' => TaxonomyResource::collection($this->whenLoaded('tags')),
            ],
            'links' => [
                'self' => route('api.v1.articles.show', $this->id),
                'web' => $this->getUrl(),
            ],
            'meta' => [
                'locale' => app()->getLocale(),
                'available_locales' => $this->getAvailableLocales(),
            ],
        ];
    }
}
```

## 8.3 Standardized API Response

```php
<?php
// app/Http/Responses/ApiResponse.php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ], $meta),
        ], $status);
    }

    public static function error(
        string $message,
        int $status = 400,
        array $errors = [],
        ?string $code = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code ?? "ERR_{$status}",
                'details' => $errors,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ], $status);
    }

    public static function paginated(
        $paginator,
        string $resourceClass
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $resourceClass::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
```

## 8.4 Contract/Interface Pattern

```php
<?php
// modules/Content/Contracts/ArticleRepositoryContract.php

namespace Modules\Content\Contracts;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\DTOs\ArticleFiltersDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryContract
{
    public function findById(string $id): ?Article;
    
    public function findBySlug(string $slug, string $locale): ?Article;
    
    public function list(ArticleFiltersDTO $filters): LengthAwarePaginator;
    
    public function save(Article $article): Article;
    
    public function delete(Article $article): bool;
    
    public function published(): self;
    
    public function featured(): self;
}
```

```php
<?php
// modules/Content/Contracts/ArticleServiceContract.php

namespace Modules\Content\Contracts;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\DTOs\CreateArticleDTO;
use Modules\Content\Domain\DTOs\UpdateArticleDTO;

interface ArticleServiceContract
{
    public function create(CreateArticleDTO $data): Article;
    
    public function update(Article $article, UpdateArticleDTO $data): Article;
    
    public function publish(Article $article): Article;
    
    public function unpublish(Article $article): Article;
    
    public function schedule(Article $article, \DateTimeInterface $publishAt): Article;
    
    public function archive(Article $article): Article;
    
    public function delete(Article $article): bool;
}
```

## 8.5 DTOs (Data Transfer Objects)

```php
<?php
// modules/Content/Domain/DTOs/CreateArticleDTO.php

namespace Modules\Content\Domain\DTOs;

use Illuminate\Http\Request;

final readonly class CreateArticleDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $authorId,
        public ?string $excerpt = null,
        public ?string $slug = null,
        public string $type = 'post',
        public ?string $featuredImageId = null,
        public array $categoryIds = [],
        public array $tagIds = [],
        public array $translations = [],
        public array $seo = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            content: $request->validated('content'),
            authorId: $request->validated('author_id', auth()->id()),
            excerpt: $request->validated('excerpt'),
            slug: $request->validated('slug'),
            type: $request->validated('type', 'post'),
            featuredImageId: $request->validated('featured_image_id'),
            categoryIds: $request->validated('category_ids', []),
            tagIds: $request->validated('tag_ids', []),
            translations: $request->validated('translations', []),
            seo: $request->validated('seo', []),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'author_id' => $this->authorId,
            'excerpt' => $this->excerpt,
            'slug' => $this->slug,
            'type' => $this->type,
            'featured_image_id' => $this->featuredImageId,
        ];
    }
}
```

## 8.6 Deprecation Policy

```php
<?php
// Middleware for API deprecation warnings

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class ApiDeprecationMiddleware
{
    private array $deprecations = [
        'v1' => [
            'articles.list' => [
                'deprecated_at' => '2024-06-01',
                'sunset_at' => '2024-12-01',
                'replacement' => 'v2/articles',
                'fields' => [
                    'excerpt' => 'Use "summary" instead',
                ],
            ],
        ],
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $version = $request->segment(2); // api/v1/...
        $route = $request->route()?->getName();

        if ($deprecation = $this->getDeprecation($version, $route)) {
            $response->headers->set('Deprecation', $deprecation['deprecated_at']);
            $response->headers->set('Sunset', $deprecation['sunset_at']);
            $response->headers->set('Link', "<{$deprecation['replacement']}>; rel=\"successor-version\"");
        }

        return $response;
    }

    private function getDeprecation(string $version, ?string $route): ?array
    {
        return $this->deprecations[$version][$route] ?? null;
    }
}
```

---

# 9. Extensibility Points & Integration Patterns

## 9.1 Event-Driven Architecture

```php
<?php
// Core events that modules can listen to

// Content events
Modules\Content\Events\ContentCreated::class
Modules\Content\Events\ContentUpdated::class
Modules\Content\Events\ContentPublished::class
Modules\Content\Events\ContentUnpublished::class
Modules\Content\Events\ContentArchived::class
Modules\Content\Events\ContentDeleted::class

// Media events
Modules\Media\Events\MediaUploaded::class
Modules\Media\Events\MediaProcessed::class
Modules\Media\Events\MediaDeleted::class

// User events
Modules\Users\Events\UserRegistered::class
Modules\Users\Events\UserUpdated::class
Modules\Users\Events\UserDeleted::class

// Ecommerce events
Modules\Ecommerce\Events\ProductPublished::class
Modules\Ecommerce\Events\OrderCreated::class
Modules\Ecommerce\Events\OrderStatusChanged::class
Modules\Ecommerce\Events\PaymentReceived::class
```

```php
<?php
// Module Event Service Provider

namespace Modules\Search\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Modules\Content\Events\ContentPublished;
use Modules\Content\Events\ContentDeleted;
use Modules\Search\Listeners\IndexContent;
use Modules\Search\Listeners\RemoveFromIndex;

final class SearchEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ContentPublished::class => [
            IndexContent::class,
        ],
        ContentDeleted::class => [
            RemoveFromIndex::class,
        ],
    ];

    protected $subscribe = [
        \Modules\Search\Listeners\ContentSubscriber::class,
    ];
}
```

## 9.2 Webhook System

```php
<?php
// app/Services/WebhookDispatcher.php

namespace App\Services;

use App\Models\Webhook;
use App\Jobs\DispatchWebhook;
use Illuminate\Support\Facades\Log;

final class WebhookDispatcher
{
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::query()
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            DispatchWebhook::dispatch($webhook, $event, $payload)
                ->onQueue('webhooks');
        }
    }
}
```

```php
<?php
// app/Jobs/DispatchWebhook.php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

final class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $startTime = microtime(true);

        $requestPayload = [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $this->payload,
        ];

        $signature = $this->generateSignature($requestPayload);

        try {
            $response = Http::timeout($this->webhook->timeout ?? 30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $this->event,
                    ...$this->webhook->headers ?? [],
                ])
                ->post($this->webhook->url, $requestPayload);

            $this->logWebhook($response, microtime(true) - $startTime);

            if ($response->failed()) {
                throw new \Exception("Webhook failed: {$response->status()}");
            }

            $this->webhook->increment('success_count');
            $this->webhook->update(['last_triggered_at' => now(), 'last_status' => 'success']);

        } catch (\Exception $e) {
            $this->webhook->increment('failure_count');
            $this->webhook->update(['last_status' => 'failed']);

            if ($this->attempts() >= $this->tries) {
                $this->logWebhook(null, microtime(true) - $startTime, $e->getMessage());
            }

            throw $e;
        }
    }

    private function generateSignature(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), $this->webhook->secret ?? '');
    }

    private function logWebhook($response, float $duration, ?string $error = null): void
    {
        WebhookLog::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'request_payload' => $this->payload,
            'response_status' => $response?->status(),
            'response_body' => $response?->body(),
            'duration_ms' => (int)($duration * 1000),
            'status' => $error ? 'failed' : 'success',
            'error_message' => $error,
            'attempt' => $this->attempts(),
        ]);
    }
}
```

## 9.3 Plugin Hooks (Lifecycle)

```php
<?php
// Trait for hookable models

namespace App\Traits;

trait Hookable
{
    public static function bootHookable(): void
    {
        static::creating(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.before_create', $model);
        });

        static::created(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.after_create', $model);
        });

        static::updating(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.before_update', $model);
        });

        static::updated(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.after_update', $model);
        });

        static::deleting(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.before_delete', $model);
        });

        static::deleted(function ($model) {
            app('hooks')->doAction(static::getHookPrefix() . '.after_delete', $model);
        });
    }

    protected static function getHookPrefix(): string
    {
        return strtolower(class_basename(static::class));
    }
}
```

## 9.4 Adapter Pattern for Integrations

```php
<?php
// contracts/SearchEngineContract.php

namespace App\Contracts;

interface SearchEngineContract
{
    public function index(string $index, string $id, array $data): bool;
    public function delete(string $index, string $id): bool;
    public function search(string $index, array $query): array;
    public function bulk(string $index, array $operations): bool;
}
```

```php
<?php
// adapters/MeilisearchAdapter.php

namespace App\Adapters\Search;

use App\Contracts\SearchEngineContract;
use Meilisearch\Client;

final class MeilisearchAdapter implements SearchEngineContract
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function index(string $index, string $id, array $data): bool
    {
        $this->client->index($index)->addDocuments([
            array_merge(['id' => $id], $data)
        ]);
        return true;
    }

    public function delete(string $index, string $id): bool
    {
        $this->client->index($index)->deleteDocument($id);
        return true;
    }

    public function search(string $index, array $query): array
    {
        return $this->client->index($index)->search(
            $query['q'] ?? '',
            $query['options'] ?? []
        )->toArray();
    }

    public function bulk(string $index, array $operations): bool
    {
        $this->client->index($index)->addDocuments($operations);
        return true;
    }
}
```

```php
<?php
// Service Provider binding

$this->app->bind(SearchEngineContract::class, function ($app) {
    return match(config('search.driver')) {
        'meilisearch' => new MeilisearchAdapter(new \Meilisearch\Client(
            config('search.meilisearch.host'),
            config('search.meilisearch.key')
        )),
        'elasticsearch' => new ElasticsearchAdapter(...),
        'algolia' => new AlgoliaAdapter(...),
        default => new NullSearchAdapter(),
    };
});
```

---

# 10. Multilingual & Currencies Handling

## 10.1 Translation Approach

```php
<?php
// Trait for translatable models

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelClass());
    }

    public function translate(?string $locale = null): ?object
    {
        $locale = $locale ?? App::getLocale();
        
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale'))
            ?? $this->translations->first();
    }

    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        return $this->translate($locale)?->{$field};
    }

    public function setTranslation(string $field, string $value, ?string $locale = null): void
    {
        $locale = $locale ?? App::getLocale();
        
        $translation = $this->translations()->firstOrNew(['locale' => $locale]);
        $translation->{$field} = $value;
        $translation->save();
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->contains('locale', $locale);
    }

    public function getAvailableLocales(): array
    {
        return $this->translations->pluck('locale')->toArray();
    }

    // Accessor for translated fields
    public function __get($key)
    {
        if (in_array($key, $this->getTranslatableFields())) {
            return $this->getTranslation($key);
        }
        return parent::__get($key);
    }

    abstract protected function getTranslationModelClass(): string;
    abstract protected function getTranslatableFields(): array;
}
```

## 10.2 Fallback Chain

```php
<?php
// config/localization.php

return [
    'default' => 'ar',
    'fallback' => 'en',
    
    'fallback_chains' => [
        'ar' => ['en'],
        'ar_SA' => ['ar', 'en'],
        'fr' => ['en'],
        'de' => ['en'],
    ],
    
    'available' => [
        'ar' => ['name' => 'العربية', 'direction' => 'rtl'],
        'en' => ['name' => 'English', 'direction' => 'ltr'],
        'fr' => ['name' => 'Français', 'direction' => 'ltr'],
    ],
];
```

```php
<?php
// app/Services/LocaleResolver.php

namespace App\Services;

final class LocaleResolver
{
    public function resolve(?string $locale = null): string
    {
        // Priority: Request param > User preference > Session > Browser > Default
        
        $locale = $locale
            ?? request()->query('locale')
            ?? request()->header('Accept-Language')
            ?? session('locale')
            ?? $this->detectBrowserLocale()
            ?? config('localization.default');

        return $this->normalize($locale);
    }

    public function getFallbackChain(string $locale): array
    {
        return config("localization.fallback_chains.{$locale}", [config('localization.fallback')]);
    }

    public function normalize(string $locale): string
    {
        // ar_SA -> ar if ar_SA not available
        if (!$this->isAvailable($locale) && str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }
        
        return $this->isAvailable($locale) ? $locale : config('localization.default');
    }

    public function isAvailable(string $locale): bool
    {
        return array_key_exists($locale, config('localization.available', []));
    }

    private function detectBrowserLocale(): ?string
    {
        $accept = request()->header('Accept-Language');
        if (!$accept) return null;

        // Parse Accept-Language header
        preg_match_all('/([a-z]{2}(?:-[A-Z]{2})?)/i', $accept, $matches);
        
        foreach ($matches[1] as $locale) {
            $normalized = str_replace('-', '_', $locale);
            if ($this->isAvailable($normalized)) {
                return $normalized;
            }
        }

        return null;
    }
}
```

## 10.3 Currency Handling

```php
<?php
// app/Services/CurrencyConverter.php

namespace App\Services;

use App\Contracts\ExchangeRateProviderContract;
use Illuminate\Support\Facades\Cache;

final class CurrencyConverter
{
    public function __construct(
        private readonly ExchangeRateProviderContract $rateProvider,
    ) {}

    public function convert(
        float $amount,
        string $from,
        string $to,
        ?string $date = null
    ): float {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->getRate($from, $to, $date);
        return round($amount * $rate, $this->getDecimals($to));
    }

    public function getRate(string $from, string $to, ?string $date = null): float
    {
        $cacheKey = "exchange_rate:{$from}:{$to}" . ($date ? ":{$date}" : '');

        return Cache::remember($cacheKey, 3600, function () use ($from, $to, $date) {
            return $this->rateProvider->getRate($from, $to, $date);
        });
    }

    public function format(float $amount, string $currency, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    private function getDecimals(string $currency): int
    {
        return config("currencies.{$currency}.decimals", 2);
    }
}
```

## 10.4 Exchange Rate Sync Job

```php
<?php
// app/Jobs/SyncExchangeRates.php

namespace App\Jobs;

use App\Contracts\ExchangeRateProviderContract;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SyncExchangeRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 300;

    public function handle(ExchangeRateProviderContract $provider): void
    {
        $baseCurrency = config('currency.default');
        $currencies = Currency::active()->where('code', '!=', $baseCurrency)->pluck('code');

        try {
            $rates = $provider->fetchRates($baseCurrency, $currencies->toArray());

            DB::transaction(function () use ($baseCurrency, $rates) {
                foreach ($rates as $code => $rate) {
                    // Skip frozen rates
                    $existing = ExchangeRate::where('from_currency', $baseCurrency)
                        ->where('to_currency', $code)
                        ->first();

                    if ($existing?->is_frozen) {
                        continue;
                    }

                    // Archive old rate
                    if ($existing) {
                        $existing->history()->create([
                            'rate' => $existing->rate,
                            'source' => $existing->source,
                            'recorded_at' => $existing->updated_at,
                        ]);
                    }

                    // Update or create
                    ExchangeRate::updateOrCreate(
                        ['from_currency' => $baseCurrency, 'to_currency' => $code],
                        [
                            'rate' => $rate,
                            'source' => 'api',
                            'provider' => $provider->getName(),
                            'effective_at' => now(),
                        ]
                    );
                }
            });

            Log::info('Exchange rates synced successfully', [
                'currencies' => $currencies->count(),
                'provider' => $provider->getName(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync exchange rates', [
                'error' => $e->getMessage(),
                'provider' => $provider->getName(),
            ]);

            // Use fallback rates if available
            $this->useFallbackRates();

            throw $e;
        }
    }

    private function useFallbackRates(): void
    {
        // Logic to use cached/fallback rates
    }
}
```

## 10.5 Multi-Currency Price Display

```php
<?php
// app/Services/PriceFormatter.php

namespace App\Services;

final class PriceFormatter
{
    public function __construct(
        private readonly CurrencyConverter $converter,
    ) {}

    public function format(
        float $amount,
        string $baseCurrency,
        ?string $displayCurrency = null,
        ?string $locale = null
    ): array {
        $displayCurrency = $displayCurrency ?? session('currency', config('currency.default'));
        $locale = $locale ?? app()->getLocale();

        $convertedAmount = $this->converter->convert($amount, $baseCurrency, $displayCurrency);

        return [
            'amount' => $convertedAmount,
            'formatted' => $this->converter->format($convertedAmount, $displayCurrency, $locale),
            'currency' => $displayCurrency,
            'original' => [
                'amount' => $amount,
                'currency' => $baseCurrency,
            ],
        ];
    }

    public function formatProduct(Product $product, ?string $displayCurrency = null): array
    {
        $baseCurrency = $product->base_currency ?? config('currency.default');
        
        // Check if product has price in display currency
        $directPrice = $product->prices()
            ->where('currency', $displayCurrency)
            ->first();

        if ($directPrice) {
            return [
                'amount' => $directPrice->amount,
                'formatted' => $this->converter->format($directPrice->amount, $displayCurrency),
                'currency' => $displayCurrency,
                'is_converted' => false,
            ];
        }

        // Convert from base price
        return array_merge(
            $this->format($product->base_price, $baseCurrency, $displayCurrency),
            ['is_converted' => true]
        );
    }
}
```

---

**نهاية الجزء الثالث - يتبع الجزء الرابع: CI/CD, Testing, Security**
