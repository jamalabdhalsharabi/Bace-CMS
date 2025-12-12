# دليل هيكلة CMS احترافي - Laravel 12
## الجزء الرابع: CI/CD, Testing, Security

---

# 11. Deployment & CI/CD

## 11.1 Pipeline Stages

```yaml
# .github/workflows/main.yml

name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

env:
  PHP_VERSION: '8.3'
  NODE_VERSION: '20'

jobs:
  # ═══════════════════════════════════════════════════════════
  # Stage 1: Code Quality
  # ═══════════════════════════════════════════════════════════
  lint:
    name: Lint & Static Analysis
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          tools: composer, phpstan, php-cs-fixer
          
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
        
      - name: PHP CS Fixer
        run: php-cs-fixer fix --dry-run --diff
        
      - name: PHPStan
        run: phpstan analyse --memory-limit=2G
        
      - name: Rector (dry-run)
        run: vendor/bin/rector --dry-run

  # ═══════════════════════════════════════════════════════════
  # Stage 2: Unit Tests
  # ═══════════════════════════════════════════════════════════
  unit-tests:
    name: Unit Tests
    runs-on: ubuntu-latest
    needs: lint
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: mbstring, xml, ctype, json, pdo_sqlite
          coverage: pcov
          
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
        
      - name: Run Unit Tests
        run: php artisan test --testsuite=Unit --coverage-clover=coverage.xml
        
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          files: coverage.xml

  # ═══════════════════════════════════════════════════════════
  # Stage 3: Integration Tests
  # ═══════════════════════════════════════════════════════════
  integration-tests:
    name: Integration Tests
    runs-on: ubuntu-latest
    needs: unit-tests
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
          POSTGRES_DB: cms_test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
      redis:
        image: redis:7
        ports:
          - 6379:6379
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: pdo_pgsql, redis
          
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
        
      - name: Run Migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: cms_test
          DB_USERNAME: test
          DB_PASSWORD: test
          
      - name: Run Integration Tests
        run: php artisan test --testsuite=Integration
        env:
          DB_CONNECTION: pgsql
          REDIS_HOST: localhost

  # ═══════════════════════════════════════════════════════════
  # Stage 4: Module Tests
  # ═══════════════════════════════════════════════════════════
  module-tests:
    name: Module Tests
    runs-on: ubuntu-latest
    needs: unit-tests
    strategy:
      matrix:
        module: [Core, Content, Media, Auth, Ecommerce]
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
        
      - name: Run Module Tests
        run: php artisan test --testsuite=${{ matrix.module }}

  # ═══════════════════════════════════════════════════════════
  # Stage 5: Build
  # ═══════════════════════════════════════════════════════════
  build:
    name: Build Assets
    runs-on: ubuntu-latest
    needs: [integration-tests, module-tests]
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: 'npm'
          
      - name: Install NPM Dependencies
        run: npm ci
        
      - name: Build Assets
        run: npm run build
        
      - name: Upload Artifacts
        uses: actions/upload-artifact@v4
        with:
          name: build-assets
          path: public/build/

  # ═══════════════════════════════════════════════════════════
  # Stage 6: Deploy Staging
  # ═══════════════════════════════════════════════════════════
  deploy-staging:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    needs: build
    if: github.ref == 'refs/heads/develop'
    environment: staging
    steps:
      - uses: actions/checkout@v4
      
      - name: Download Artifacts
        uses: actions/download-artifact@v4
        with:
          name: build-assets
          path: public/build/
          
      - name: Deploy to Staging
        run: |
          # Deploy using Deployer, Envoy, or custom script
          vendor/bin/dep deploy staging
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}

  # ═══════════════════════════════════════════════════════════
  # Stage 7: Deploy Production
  # ═══════════════════════════════════════════════════════════
  deploy-production:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: build
    if: github.ref == 'refs/heads/main'
    environment: production
    steps:
      - uses: actions/checkout@v4
      
      - name: Download Artifacts
        uses: actions/download-artifact@v4
        with:
          name: build-assets
          path: public/build/
          
      - name: Pre-deploy Checks
        run: |
          php artisan config:cache --env=production
          php artisan route:cache
          php artisan migrate --force --pretend
          
      - name: Deploy to Production (Blue-Green)
        run: |
          vendor/bin/dep deploy production --blue-green
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          
      - name: Run Smoke Tests
        run: |
          curl -f https://production.example.com/health || exit 1
          
      - name: Post-deploy Jobs
        run: |
          php artisan queue:restart
          php artisan cache:clear
          php artisan search:reindex --queue
```

## 11.2 Deployer Configuration

```php
<?php
// deploy.php

namespace Deployer;

require 'recipe/laravel.php';

set('application', 'cms');
set('repository', 'git@github.com:company/cms.git');
set('php_fpm_version', '8.3');
set('keep_releases', 5);

// Shared files and directories
add('shared_files', ['.env']);
add('shared_dirs', ['storage', 'public/uploads']);

// Writable directories
add('writable_dirs', ['storage', 'bootstrap/cache']);

// Hosts
host('staging')
    ->set('hostname', 'staging.example.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/cms-staging')
    ->set('branch', 'develop');

host('production')
    ->set('hostname', 'production.example.com')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/cms')
    ->set('branch', 'main');

// Tasks
task('deploy:modules', function () {
    run('cd {{release_path}} && php artisan module:publish');
});

task('deploy:permissions', function () {
    run('cd {{release_path}} && php artisan permission:cache');
});

task('deploy:search', function () {
    run('cd {{release_path}} && php artisan search:sync --queue');
});

// Blue-Green Deployment
task('deploy:blue-green', function () {
    $current = get('current_path');
    $release = get('release_path');
    
    // Switch symlink atomically
    run("ln -sfn {$release} {$current}");
    
    // Reload PHP-FPM gracefully
    run('sudo systemctl reload php{{php_fpm_version}}-fpm');
});

// Rollback hook
task('deploy:rollback:notify', function () {
    // Send notification on rollback
    run('curl -X POST {{slack_webhook}} -d \'{"text":"Deployment rolled back!"}\'');
});

after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'deploy:permissions');
after('deploy:symlink', 'deploy:modules');
```

## 11.3 Feature Flag Deployment

```php
<?php
// Deploy features progressively

// config/features.php
return [
    'new_editor' => [
        'enabled' => env('FEATURE_NEW_EDITOR', false),
        'rollout_percentage' => env('FEATURE_NEW_EDITOR_ROLLOUT', 0),
        'allowed_users' => [], // Beta testers
    ],
];

// FeatureManager with rollout support
public function enabled(string $feature, ?User $user = null): bool
{
    $config = config("features.{$feature}");
    
    if (!$config) {
        return false;
    }
    
    // Check if explicitly enabled
    if ($config['enabled'] === true) {
        return true;
    }
    
    // Check rollout percentage
    if (isset($config['rollout_percentage']) && $config['rollout_percentage'] > 0) {
        if ($user) {
            // Consistent hash for user
            $hash = crc32($user->id . $feature) % 100;
            return $hash < $config['rollout_percentage'];
        }
    }
    
    // Check allowed users
    if ($user && in_array($user->id, $config['allowed_users'] ?? [])) {
        return true;
    }
    
    return false;
}
```

---

# 12. Testing Strategy

## 12.1 Test Structure

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── ArticleServiceTest.php
│   │   └── CurrencyConverterTest.php
│   ├── Models/
│   │   └── ArticleTest.php
│   └── ValueObjects/
│       └── SlugTest.php
├── Feature/
│   ├── Api/
│   │   ├── V1/
│   │   │   ├── ArticleApiTest.php
│   │   │   └── AuthApiTest.php
│   │   └── V2/
│   ├── Admin/
│   │   ├── ArticleManagementTest.php
│   │   └── UserManagementTest.php
│   └── Web/
│       └── ArticleViewTest.php
├── Integration/
│   ├── Database/
│   │   └── ArticleRepositoryTest.php
│   └── Search/
│       └── ArticleIndexingTest.php
├── Contract/
│   ├── ArticleServiceContractTest.php
│   └── SearchEngineContractTest.php
├── E2E/
│   └── AdminWorkflowTest.php
└── Modules/
    ├── Content/
    │   ├── Unit/
    │   └── Feature/
    ├── Ecommerce/
    └── ...
```

## 12.2 Unit Test Example

```php
<?php
// tests/Unit/Services/ArticleServiceTest.php

namespace Tests\Unit\Services;

use Modules\Content\Services\ArticleService;
use Modules\Content\Contracts\ArticleRepositoryContract;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\DTOs\CreateArticleDTO;
use Illuminate\Contracts\Events\Dispatcher;
use Mockery;
use Tests\TestCase;

final class ArticleServiceTest extends TestCase
{
    private ArticleService $service;
    private $mockRepository;
    private $mockDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(ArticleRepositoryContract::class);
        $this->mockDispatcher = Mockery::mock(Dispatcher::class);
        
        $this->service = new ArticleService(
            $this->mockRepository,
            $this->mockDispatcher,
        );
    }

    public function test_create_article_saves_and_dispatches_event(): void
    {
        // Arrange
        $dto = new CreateArticleDTO(
            title: 'Test Article',
            content: 'Content here',
            authorId: 'user-uuid',
        );

        $this->mockRepository
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(fn($article) => $article);

        $this->mockDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->withArgs(fn($event) => $event instanceof \Modules\Content\Events\ArticleCreated);

        // Act
        $article = $this->service->create($dto);

        // Assert
        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Test Article', $article->title);
    }

    public function test_publish_changes_status_and_dispatches_event(): void
    {
        // Arrange
        $article = Article::factory()->make(['status' => 'draft']);

        $this->mockRepository
            ->shouldReceive('save')
            ->once();

        $this->mockDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->withArgs(fn($event) => $event instanceof \Modules\Content\Events\ArticlePublished);

        // Act
        $result = $this->service->publish($article);

        // Assert
        $this->assertEquals('published', $result->status);
        $this->assertNotNull($result->published_at);
    }

    public function test_cannot_publish_already_published_article(): void
    {
        // Arrange
        $article = Article::factory()->make(['status' => 'published']);

        // Assert
        $this->expectException(\DomainException::class);

        // Act
        $this->service->publish($article);
    }
}
```

## 12.3 Feature Test Example

```php
<?php
// tests/Feature/Api/V1/ArticleApiTest.php

namespace Tests\Feature\Api\V1;

use Modules\Content\Domain\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_can_list_published_articles(): void
    {
        // Arrange
        Article::factory()->published()->count(5)->create();
        Article::factory()->draft()->count(3)->create();

        // Act
        $response = $this->getJson('/api/v1/articles');

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'type', 'attributes' => ['title', 'slug']]
                ],
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_can_create_article_with_valid_data(): void
    {
        // Arrange
        $this->user->givePermissionTo('content.create');

        $data = [
            'title' => 'New Article',
            'content' => 'Article content here',
            'type' => 'post',
        ];

        // Act
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/articles', $data);

        // Assert
        $response->assertCreated()
            ->assertJsonPath('data.attributes.title', 'New Article');

        $this->assertDatabaseHas('articles', ['title' => 'New Article']);
    }

    public function test_cannot_create_article_without_permission(): void
    {
        // Act
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/articles', [
                'title' => 'Test',
                'content' => 'Content',
            ]);

        // Assert
        $response->assertForbidden();
    }

    public function test_can_filter_articles_by_category(): void
    {
        // Arrange
        $category = \Modules\Taxonomy\Models\Taxonomy::factory()->create();
        Article::factory()->published()->hasAttached($category)->count(3)->create();
        Article::factory()->published()->count(2)->create();

        // Act
        $response = $this->getJson("/api/v1/articles?category={$category->slug}");

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
```

## 12.4 Contract Test

```php
<?php
// tests/Contract/ArticleServiceContractTest.php

namespace Tests\Contract;

use Modules\Content\Contracts\ArticleServiceContract;
use Modules\Content\Domain\DTOs\CreateArticleDTO;
use Modules\Content\Domain\Models\Article;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ArticleServiceContractTest extends TestCase
{
    use RefreshDatabase;

    private ArticleServiceContract $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ArticleServiceContract::class);
    }

    public function test_create_returns_article_instance(): void
    {
        $dto = new CreateArticleDTO(
            title: 'Test',
            content: 'Content',
            authorId: \App\Models\User::factory()->create()->id,
        );

        $result = $this->service->create($dto);

        $this->assertInstanceOf(Article::class, $result);
    }

    public function test_publish_returns_published_article(): void
    {
        $article = Article::factory()->draft()->create();

        $result = $this->service->publish($article);

        $this->assertEquals('published', $result->status);
        $this->assertNotNull($result->published_at);
    }

    // All methods defined in contract should be tested
}
```

## 12.5 Migration Test

```php
<?php
// tests/Integration/Database/MigrationTest.php

namespace Tests\Integration\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MigrationTest extends TestCase
{
    public function test_articles_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('articles'));
        
        $this->assertTrue(Schema::hasColumns('articles', [
            'id',
            'status',
            'type',
            'author_id',
            'featured_image_id',
            'is_featured',
            'published_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_article_translations_table_has_expected_structure(): void
    {
        $this->assertTrue(Schema::hasTable('article_translations'));
        
        $this->assertTrue(Schema::hasColumns('article_translations', [
            'id',
            'article_id',
            'locale',
            'title',
            'slug',
            'content',
        ]));

        // Check unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        \DB::table('article_translations')->insert([
            ['id' => \Str::uuid(), 'article_id' => 'test', 'locale' => 'en', 'title' => 'Test', 'slug' => 'test'],
            ['id' => \Str::uuid(), 'article_id' => 'test', 'locale' => 'en', 'title' => 'Test2', 'slug' => 'test2'],
        ]);
    }
}
```

## 12.6 Job Test

```php
<?php
// tests/Feature/Jobs/PublishArticleJobTest.php

namespace Tests\Feature\Jobs;

use Modules\Content\Jobs\PublishArticle;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Events\ArticlePublished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class PublishArticleJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_publishes_article(): void
    {
        // Arrange
        $article = Article::factory()->draft()->create();
        Event::fake([ArticlePublished::class]);

        // Act
        PublishArticle::dispatch($article);

        // Assert
        $this->assertEquals('published', $article->fresh()->status);
        Event::assertDispatched(ArticlePublished::class);
    }

    public function test_job_is_queued_on_correct_queue(): void
    {
        Queue::fake();

        $article = Article::factory()->draft()->create();

        PublishArticle::dispatch($article);

        Queue::assertPushedOn('content', PublishArticle::class);
    }
}
```

---

# 13. Observability & Operations

## 13.1 Logging Configuration

```php
<?php
// config/logging.php

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'stderr'],
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => JsonFormatter::class, // Structured JSON for log aggregation
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        // Separate channels per concern
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 90,
        ],

        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => 365,
        ],

        'jobs' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jobs.log'),
            'level' => 'info',
            'days' => 30,
        ],
    ],
];
```

## 13.2 Structured Logging

```php
<?php
// app/Logging/ContextProcessor.php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class ContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        
        $extra['request_id'] = request()->header('X-Request-ID') ?? \Str::uuid()->toString();
        $extra['user_id'] = auth()->id();
        $extra['tenant_id'] = tenant()?->id;
        $extra['ip'] = request()->ip();
        $extra['url'] = request()->fullUrl();
        $extra['method'] = request()->method();
        
        return $record->with(extra: $extra);
    }
}
```

## 13.3 Metrics & Monitoring

```php
<?php
// app/Services/MetricsCollector.php

namespace App\Services;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Histogram;

final class MetricsCollector
{
    private Counter $requestCounter;
    private Histogram $requestDuration;
    private Counter $jobCounter;

    public function __construct(CollectorRegistry $registry)
    {
        $this->requestCounter = $registry->getOrRegisterCounter(
            'cms',
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'route', 'status']
        );

        $this->requestDuration = $registry->getOrRegisterHistogram(
            'cms',
            'http_request_duration_seconds',
            'HTTP request duration',
            ['method', 'route'],
            [0.01, 0.05, 0.1, 0.5, 1, 5]
        );

        $this->jobCounter = $registry->getOrRegisterCounter(
            'cms',
            'jobs_total',
            'Total jobs processed',
            ['job', 'status']
        );
    }

    public function recordRequest(string $method, string $route, int $status, float $duration): void
    {
        $this->requestCounter->incBy(1, [$method, $route, (string)$status]);
        $this->requestDuration->observe($duration, [$method, $route]);
    }

    public function recordJob(string $job, string $status): void
    {
        $this->jobCounter->incBy(1, [$job, $status]);
    }
}
```

## 13.4 Health Check Endpoint

```php
<?php
// app/Http/Controllers/HealthController.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = !in_array(false, array_column($checks, 'healthy'));
        $status = $healthy ? 200 : 503;

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $status);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = (microtime(true) - $start) * 1000;

            return ['healthy' => true, 'latency_ms' => round($latency, 2)];
        } catch (\Exception $e) {
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $latency = (microtime(true) - $start) * 1000;

            return ['healthy' => true, 'latency_ms' => round($latency, 2)];
        } catch (\Exception $e) {
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $size = Queue::size();
            return [
                'healthy' => true,
                'queue_size' => $size,
                'warning' => $size > 1000 ? 'Queue backlog detected' : null,
            ];
        } catch (\Exception $e) {
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path();
        $free = disk_free_space($path);
        $total = disk_total_space($path);
        $usedPercent = (($total - $free) / $total) * 100;

        return [
            'healthy' => $usedPercent < 90,
            'used_percent' => round($usedPercent, 2),
            'warning' => $usedPercent > 80 ? 'Disk space running low' : null,
        ];
    }
}
```

## 13.5 Queue Monitoring

```php
<?php
// app/Console/Commands/QueueMonitor.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

final class QueueMonitor extends Command
{
    protected $signature = 'queue:monitor {--alert-threshold=100}';
    protected $description = 'Monitor queue depths and alert if threshold exceeded';

    public function handle(): int
    {
        $queues = ['default', 'content', 'media', 'notifications', 'webhooks'];
        $threshold = (int) $this->option('alert-threshold');

        $this->table(
            ['Queue', 'Size', 'Failed', 'Status'],
            collect($queues)->map(function ($queue) use ($threshold) {
                $size = Queue::size($queue);
                $failed = $this->getFailedCount($queue);
                $status = $size > $threshold ? '⚠️ HIGH' : '✅ OK';

                if ($size > $threshold) {
                    $this->alert("Queue {$queue} has {$size} pending jobs!");
                }

                return [$queue, $size, $failed, $status];
            })->toArray()
        );

        return Command::SUCCESS;
    }

    private function getFailedCount(string $queue): int
    {
        return \DB::table('failed_jobs')
            ->where('queue', $queue)
            ->where('failed_at', '>=', now()->subHour())
            ->count();
    }
}
```

---

# 14. Security & Compliance

## 14.1 RBAC Implementation

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    public function hasModuleAccess(string $module): bool
    {
        return $this->hasPermissionTo("{$module}.access") 
            || $this->hasRole('super_admin');
    }

    public function canManageContent(string $type, string $action): bool
    {
        return $this->hasPermissionTo("{$type}.{$action}");
    }
}
```

```php
<?php
// Permission structure

// Core permissions
'users.view', 'users.create', 'users.update', 'users.delete'
'roles.view', 'roles.create', 'roles.update', 'roles.delete'
'settings.view', 'settings.update'

// Content permissions (per content type)
'articles.view', 'articles.create', 'articles.update', 'articles.delete', 'articles.publish'
'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish'
'products.view', 'products.create', 'products.update', 'products.delete', 'products.publish'

// Module permissions
'ecommerce.access', 'ecommerce.manage_orders', 'ecommerce.manage_inventory'
'analytics.view', 'analytics.export'
```

## 14.2 Policy Implementation

```php
<?php
// modules/Content/Policies/ArticlePolicy.php

namespace Modules\Content\Policies;

use App\Models\User;
use Modules\Content\Domain\Models\Article;

final class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('articles.view');
    }

    public function view(User $user, Article $article): bool
    {
        if ($article->status === 'published') {
            return true;
        }

        return $user->hasPermissionTo('articles.view')
            || $article->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasPermissionTo('articles.update')) {
            return true;
        }

        // Authors can update their own drafts
        return $article->author_id === $user->id 
            && $article->status === 'draft';
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('articles.delete');
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('articles.publish');
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->hasRole('super_admin');
    }
}
```

## 14.3 Input Sanitization

```php
<?php
// app/Http/Middleware/SanitizeInput.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class SanitizeInput
{
    private array $except = [
        'password',
        'password_confirmation',
        'content', // Rich text fields
    ];

    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        $sanitized = $this->sanitize($input);
        $request->merge($sanitized);

        return $next($request);
    }

    private function sanitize(array $data, string $prefix = ''): array
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (in_array($fullKey, $this->except)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value, $fullKey);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    private function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Normalize line endings
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return $value;
    }
}
```

## 14.4 Rate Limiting

```php
<?php
// app/Providers/RouteServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    RateLimiter::for('uploads', function (Request $request) {
        return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('webhooks', function (Request $request) {
        return Limit::perMinute(100)->by($request->ip());
    });
}
```

## 14.5 File Upload Security

```php
<?php
// app/Services/FileUploadValidator.php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class FileUploadValidator
{
    private array $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps',
        'exe', 'bat', 'cmd', 'sh', 'bash',
        'js', 'vbs', 'wsf', 'wsh',
    ];

    private array $allowedMimeTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'video' => ['video/mp4', 'video/webm', 'video/quicktime'],
        'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg'],
    ];

    public function validate(UploadedFile $file, string $type = 'image'): bool
    {
        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $this->dangerousExtensions)) {
            throw new \InvalidArgumentException("File extension '{$extension}' is not allowed");
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = $this->allowedMimeTypes[$type] ?? [];
        if (!empty($allowedMimes) && !in_array($mimeType, $allowedMimes)) {
            throw new \InvalidArgumentException("MIME type '{$mimeType}' is not allowed for {$type}");
        }

        // Check file content (magic bytes)
        $this->validateMagicBytes($file);

        // Scan for malware (if ClamAV available)
        $this->scanForMalware($file);

        return true;
    }

    private function validateMagicBytes(UploadedFile $file): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $bytes = fread($handle, 8);
        fclose($handle);

        // Check for PHP opening tags in "image" files
        if (str_contains($bytes, '<?php') || str_contains($bytes, '<?=')) {
            throw new \InvalidArgumentException('File contains executable code');
        }
    }

    private function scanForMalware(UploadedFile $file): void
    {
        if (!extension_loaded('clamav')) {
            return;
        }

        // ClamAV scan implementation
    }
}
```

## 14.6 GDPR Compliance

```php
<?php
// app/Services/GdprService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class GdprService
{
    public function exportUserData(User $user): array
    {
        return [
            'profile' => $user->toArray(),
            'articles' => $user->articles()->get()->toArray(),
            'comments' => $user->comments()->get()->toArray(),
            'orders' => $user->orders()->get()->toArray(),
            'activity_logs' => $user->activityLogs()->get()->toArray(),
        ];
    }

    public function deleteUserData(User $user, bool $anonymize = true): void
    {
        DB::transaction(function () use ($user, $anonymize) {
            if ($anonymize) {
                // Anonymize instead of delete (for referential integrity)
                $user->update([
                    'email' => "deleted_{$user->id}@anonymized.local",
                    'name' => 'Deleted User',
                    'password' => bcrypt(\Str::random(32)),
                ]);
                
                $user->profile()->update([
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => null,
                    'address' => null,
                ]);
            } else {
                // Hard delete
                $user->forceDelete();
            }

            // Delete personal files
            Storage::deleteDirectory("users/{$user->id}");

            // Log for compliance
            \Log::channel('audit')->info('User data deleted', [
                'user_id' => $user->id,
                'anonymized' => $anonymize,
                'deleted_at' => now(),
            ]);
        });
    }

    public function getConsentStatus(User $user): array
    {
        return [
            'marketing_emails' => $user->consents()->where('type', 'marketing')->first()?->granted ?? false,
            'analytics' => $user->consents()->where('type', 'analytics')->first()?->granted ?? false,
            'third_party_sharing' => $user->consents()->where('type', 'third_party')->first()?->granted ?? false,
        ];
    }
}
```

---

**نهاية الجزء الرابع - يتبع الجزء الخامس: DX, Packaging, Examples**
