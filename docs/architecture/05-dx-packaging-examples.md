# دليل هيكلة CMS احترافي - Laravel 12
## الجزء الخامس: DX, Packaging, Examples

---

# 15. Developer DX & Onboarding

## 15.1 Recommended Repo Structure

```
cms/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ModuleMake.php
│   │       ├── ModuleMigrate.php
│   │       └── ProfileSeed.php
│   ├── Contracts/
│   │   ├── SearchEngineContract.php
│   │   └── CacheContract.php
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Resources/
│   ├── Models/
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── ModuleServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Services/
│   │   ├── FeatureManager.php
│   │   ├── HookManager.php
│   │   └── ProfileLoader.php
│   └── Traits/
├── bootstrap/
│   ├── app.php
│   ├── modules.php          # Module configuration
│   └── providers.php
├── config/
│   ├── app.php
│   ├── features.php         # Feature flags
│   ├── modules.php
│   └── profiles/            # Client profiles
│       ├── default.yaml
│       └── client-example.yaml
├── database/
│   ├── factories/
│   ├── migrations/          # Core migrations only
│   └── seeders/
├── modules/                  # ⭐ All modules here
│   ├── Core/
│   ├── Content/
│   ├── Media/
│   ├── Auth/
│   ├── Users/
│   ├── Taxonomy/
│   ├── Localization/
│   ├── Currency/
│   ├── Menu/
│   ├── Search/
│   ├── Forms/
│   ├── Comments/
│   ├── Ecommerce/
│   ├── Pricing/
│   ├── Events/
│   ├── Notifications/
│   ├── Analytics/
│   └── Themes/
├── packages/                 # ⭐ Extractable packages
│   └── laravel-cms-core/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── storage/
├── tests/
│   ├── Unit/
│   ├── Feature/
│   ├── Integration/
│   ├── Contract/
│   └── Modules/
├── stubs/                    # ⭐ Code generation stubs
│   ├── module/
│   ├── controller.stub
│   ├── service.stub
│   └── repository.stub
├── .env.example
├── .github/
│   └── workflows/
│       └── main.yml
├── docker/
│   ├── Dockerfile
│   ├── nginx.conf
│   └── php.ini
├── docker-compose.yml
├── composer.json
├── package.json
├── phpstan.neon
├── phpunit.xml
├── rector.php
└── README.md
```

## 15.2 Docker Development Setup

```yaml
# docker-compose.yml

version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    volumes:
      - .:/var/www/html
      - ./docker/php.ini:/usr/local/etc/php/conf.d/custom.ini
    environment:
      - APP_ENV=local
      - DB_HOST=postgres
      - REDIS_HOST=redis
      - MEILISEARCH_HOST=meilisearch
    depends_on:
      - postgres
      - redis
      - meilisearch

  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: cms
      POSTGRES_USER: cms
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  meilisearch:
    image: getmeili/meilisearch:v1.6
    environment:
      MEILI_MASTER_KEY: masterKey
    volumes:
      - meilisearch_data:/meili_data
    ports:
      - "7700:7700"

  mailpit:
    image: axllent/mailpit
    ports:
      - "8025:8025"
      - "1025:1025"

  # Queue worker
  worker:
    build:
      context: .
      dockerfile: docker/Dockerfile
    command: php artisan queue:work --queue=default,content,media,notifications
    volumes:
      - .:/var/www/html
    depends_on:
      - postgres
      - redis

  # Scheduler
  scheduler:
    build:
      context: .
      dockerfile: docker/Dockerfile
    command: php artisan schedule:work
    volumes:
      - .:/var/www/html
    depends_on:
      - postgres
      - redis

volumes:
  postgres_data:
  meilisearch_data:
```

```dockerfile
# docker/Dockerfile

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd zip pcntl

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

USER www-data
```

## 15.3 Module Scaffolding Command

```php
<?php
// app/Console/Commands/ModuleMake.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class ModuleMake extends Command
{
    protected $signature = 'module:make 
                            {name : The module name (PascalCase)}
                            {--with-all : Generate all scaffolding}
                            {--model= : Generate model(s)}
                            {--api : Generate API controllers}
                            {--admin : Generate admin controllers}';

    protected $description = 'Create a new module with scaffolding';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $modulePath = base_path("modules/{$name}");

        if (File::isDirectory($modulePath)) {
            $this->error("Module {$name} already exists!");
            return Command::FAILURE;
        }

        $this->info("Creating module: {$name}");

        // Create directory structure
        $this->createDirectories($modulePath);

        // Generate files
        $this->generateServiceProvider($name, $modulePath);
        $this->generateModuleJson($name, $modulePath);
        $this->generateConfig($name, $modulePath);
        $this->generateRoutes($name, $modulePath);

        if ($this->option('with-all') || $this->option('model')) {
            $models = $this->option('model') ? explode(',', $this->option('model')) : [$name];
            foreach ($models as $model) {
                $this->generateModel(trim($model), $name, $modulePath);
            }
        }

        if ($this->option('with-all') || $this->option('api')) {
            $this->generateApiController($name, $modulePath);
        }

        if ($this->option('with-all') || $this->option('admin')) {
            $this->generateAdminController($name, $modulePath);
        }

        // Register module
        $this->registerModule($name);

        $this->info("Module {$name} created successfully!");
        $this->newLine();
        $this->line("Next steps:");
        $this->line("  1. Review modules/{$name}/module.json");
        $this->line("  2. Run: php artisan module:migrate {$name}");
        $this->line("  3. Run: php artisan module:seed {$name}");

        return Command::SUCCESS;
    }

    protected function createDirectories(string $path): void
    {
        $directories = [
            'Config',
            'Console/Commands',
            'Contracts',
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Domain/Models',
            'Domain/DTOs',
            'Domain/Enums',
            'Events',
            'Http/Controllers/Api',
            'Http/Controllers/Admin',
            'Http/Requests',
            'Http/Resources',
            'Http/Middleware',
            'Jobs',
            'Listeners',
            'Policies',
            'Providers',
            'Repositories',
            'Routes',
            'Services',
            'Tests/Feature',
            'Tests/Unit',
            'Views/admin',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$path}/{$dir}", 0755, true);
            File::put("{$path}/{$dir}/.gitkeep", '');
        }
    }

    protected function generateServiceProvider(string $name, string $path): void
    {
        $stub = File::get(base_path('stubs/module/service-provider.stub'));
        $content = str_replace(
            ['{{moduleName}}', '{{moduleNameLower}}'],
            [$name, Str::lower($name)],
            $stub
        );
        File::put("{$path}/Providers/{$name}ServiceProvider.php", $content);
    }

    protected function generateModuleJson(string $name, string $path): void
    {
        $json = [
            'name' => $name,
            'alias' => Str::lower($name),
            'description' => "{$name} module",
            'version' => '1.0.0',
            'priority' => 50,
            'providers' => [
                "Modules\\{$name}\\Providers\\{$name}ServiceProvider"
            ],
            'requires' => ['core' => '^1.0'],
            'features' => [],
            'permissions' => [
                Str::lower($name) . '.view',
                Str::lower($name) . '.create',
                Str::lower($name) . '.update',
                Str::lower($name) . '.delete',
            ],
        ];

        File::put("{$path}/module.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function generateModel(string $model, string $module, string $path): void
    {
        $stub = File::get(base_path('stubs/module/model.stub'));
        $content = str_replace(
            ['{{moduleName}}', '{{modelName}}', '{{tableName}}'],
            [$module, $model, Str::snake(Str::plural($model))],
            $stub
        );
        File::put("{$path}/Domain/Models/{$model}.php", $content);

        // Generate migration
        $this->generateMigration($model, $module, $path);

        // Generate factory
        $this->generateFactory($model, $module, $path);
    }

    protected function registerModule(string $name): void
    {
        $configPath = base_path('bootstrap/modules.php');
        $config = require $configPath;

        $config['enabled'][$name] = [
            'priority' => 50,
            'required' => false,
        ];

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        File::put($configPath, $content);
    }

    // ... more generation methods
}
```

## 15.4 Stub Files

```php
<?php
// stubs/module/service-provider.stub

namespace Modules\{{moduleName}}\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

final class {{moduleName}}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/{{moduleNameLower}}.php', '{{moduleNameLower}}');
    }

    public function boot(): void
    {
        if (!app('features')->moduleEnabled('{{moduleName}}')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Views', '{{moduleNameLower}}');
        $this->loadTranslationsFrom(__DIR__.'/../Lang', '{{moduleNameLower}}');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../Routes/api.php');

        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin')
            ->group(__DIR__.'/../Routes/admin.php');
    }
}
```

```php
<?php
// stubs/module/model.stub

namespace Modules\{{moduleName}}\Domain\Models;

use App\Traits\HasTranslations;
use App\Traits\HasRevisions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class {{modelName}} extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasTranslations, HasRevisions;

    protected $table = '{{tableName}}';

    protected $fillable = [
        'status',
        // Add more fields
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected function getTranslationModelClass(): string
    {
        return {{modelName}}Translation::class;
    }

    protected function getTranslatableFields(): array
    {
        return ['title', 'slug', 'content'];
    }
}
```

## 15.5 Code Style & Static Analysis

```neon
# phpstan.neon

includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 8
    paths:
        - app
        - modules
    excludePaths:
        - modules/*/Tests
    checkGenericClassInNonGenericObjectType: false
    reportUnmatchedIgnoredErrors: false
```

```php
<?php
// rector.php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/modules',
    ])
    ->withSkip([
        __DIR__ . '/modules/*/Tests',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
    ]);
```

---

# 16. Packaging & Distribution

## 16.1 Converting Module to Composer Package

```json
// modules/Content/composer.json

{
    "name": "cms/content-module",
    "description": "Content management module for CMS",
    "type": "laravel-module",
    "version": "1.0.0",
    "license": "proprietary",
    "authors": [
        {
            "name": "Your Company",
            "email": "dev@company.com"
        }
    ],
    "require": {
        "php": "^8.3",
        "cms/core-module": "^1.0",
        "cms/media-module": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Modules\\Content\\": ""
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Modules\\Content\\Providers\\ContentServiceProvider"
            ]
        },
        "cms": {
            "module": true,
            "priority": 35
        }
    }
}
```

## 16.2 Module Installer

```php
<?php
// packages/cms-installer/src/ModuleInstaller.php

namespace CMS\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

final class ModuleInstaller extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package): string
    {
        $extra = $package->getExtra();
        
        if (isset($extra['cms']['module'])) {
            $name = $this->getModuleName($package);
            return "modules/{$name}";
        }

        return parent::getInstallPath($package);
    }

    public function supports(string $packageType): bool
    {
        return $packageType === 'laravel-module';
    }

    private function getModuleName(PackageInterface $package): string
    {
        $name = $package->getPrettyName();
        $parts = explode('/', $name);
        return ucfirst(str_replace('-module', '', end($parts)));
    }
}
```

## 16.3 Version Management

```php
<?php
// app/Console/Commands/ModuleVersion.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ModuleVersion extends Command
{
    protected $signature = 'module:version 
                            {module : Module name}
                            {version? : New version}
                            {--major : Bump major version}
                            {--minor : Bump minor version}
                            {--patch : Bump patch version}';

    public function handle(): int
    {
        $module = $this->argument('module');
        $jsonPath = module_path($module, 'module.json');

        if (!File::exists($jsonPath)) {
            $this->error("Module {$module} not found");
            return Command::FAILURE;
        }

        $json = json_decode(File::get($jsonPath), true);
        $currentVersion = $json['version'] ?? '1.0.0';

        if ($this->argument('version')) {
            $newVersion = $this->argument('version');
        } else {
            $newVersion = $this->bumpVersion($currentVersion);
        }

        $json['version'] = $newVersion;
        File::put($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Module {$module} version updated: {$currentVersion} → {$newVersion}");

        return Command::SUCCESS;
    }

    private function bumpVersion(string $version): string
    {
        [$major, $minor, $patch] = explode('.', $version);

        if ($this->option('major')) {
            return ((int)$major + 1) . '.0.0';
        }
        if ($this->option('minor')) {
            return "{$major}." . ((int)$minor + 1) . '.0';
        }
        
        return "{$major}.{$minor}." . ((int)$patch + 1);
    }
}
```

---

# 17. Operational Tasks & Cleanup

## 17.1 Orphan Media Cleanup

```php
<?php
// app/Console/Commands/CleanupOrphanMedia.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Models\Media;

final class CleanupOrphanMedia extends Command
{
    protected $signature = 'media:cleanup 
                            {--dry-run : Show what would be deleted}
                            {--older-than=30 : Days since last use}
                            {--force : Skip confirmation}';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $days = (int) $this->option('older-than');

        $this->info($dryRun ? 'DRY RUN - No files will be deleted' : 'Finding orphan media...');

        // Find media not referenced anywhere
        $orphans = Media::query()
            ->whereDoesntHave('attachments')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        if ($orphans->isEmpty()) {
            $this->info('No orphan media found.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$orphans->count()} orphan media files.");

        $totalSize = $orphans->sum('size');
        $this->line("Total size: " . $this->formatBytes($totalSize));

        if ($dryRun) {
            $this->table(
                ['ID', 'Filename', 'Size', 'Created'],
                $orphans->take(20)->map(fn($m) => [
                    $m->id,
                    $m->filename,
                    $this->formatBytes($m->size),
                    $m->created_at->diffForHumans(),
                ])
            );
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Delete these files?')) {
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($orphans->count());

        foreach ($orphans as $media) {
            // Delete file
            Storage::disk($media->disk)->delete($media->path);
            
            // Delete variants
            foreach ($media->variants as $variant) {
                Storage::disk($media->disk)->delete($variant->path);
            }

            // Delete record
            $media->forceDelete();
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Deleted {$orphans->count()} orphan media files.");

        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

## 17.2 Database Maintenance

```php
<?php
// app/Console/Commands/DatabaseMaintenance.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DatabaseMaintenance extends Command
{
    protected $signature = 'db:maintenance 
                            {--vacuum : Run VACUUM ANALYZE (PostgreSQL)}
                            {--reindex : Rebuild indexes}
                            {--cleanup-logs : Clean old logs}
                            {--days=90 : Days to keep logs}';

    public function handle(): int
    {
        if ($this->option('vacuum')) {
            $this->vacuum();
        }

        if ($this->option('reindex')) {
            $this->reindex();
        }

        if ($this->option('cleanup-logs')) {
            $this->cleanupLogs((int) $this->option('days'));
        }

        return Command::SUCCESS;
    }

    private function vacuum(): void
    {
        $this->info('Running VACUUM ANALYZE...');
        
        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        
        foreach ($tables as $table) {
            DB::statement("VACUUM ANALYZE {$table->tablename}");
            $this->line("  ✓ {$table->tablename}");
        }
    }

    private function reindex(): void
    {
        $this->info('Rebuilding indexes...');
        DB::statement('REINDEX DATABASE CONCURRENTLY ' . config('database.connections.pgsql.database'));
    }

    private function cleanupLogs(int $days): void
    {
        $this->info("Cleaning logs older than {$days} days...");

        $tables = [
            'activity_logs' => 'created_at',
            'webhook_logs' => 'created_at',
            'email_logs' => 'created_at',
            'page_views' => 'created_at',
            'search_logs' => 'created_at',
        ];

        foreach ($tables as $table => $column) {
            $deleted = DB::table($table)
                ->where($column, '<', now()->subDays($days))
                ->delete();
            
            $this->line("  ✓ {$table}: {$deleted} rows deleted");
        }
    }
}
```

## 17.3 Search Reindexing

```php
<?php
// app/Console/Commands/SearchReindex.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\SearchEngineContract;

final class SearchReindex extends Command
{
    protected $signature = 'search:reindex 
                            {index? : Specific index to reindex}
                            {--queue : Queue the reindexing}
                            {--fresh : Drop and recreate index}';

    public function handle(SearchEngineContract $search): int
    {
        $indexes = $this->argument('index') 
            ? [$this->argument('index')]
            : ['articles', 'products', 'pages', 'services'];

        foreach ($indexes as $index) {
            $this->reindexIndex($index, $search);
        }

        return Command::SUCCESS;
    }

    private function reindexIndex(string $index, SearchEngineContract $search): void
    {
        $this->info("Reindexing: {$index}");

        if ($this->option('fresh')) {
            $search->deleteIndex($index);
            $search->createIndex($index);
        }

        $modelClass = $this->getModelClass($index);
        $query = $modelClass::query()->published();
        $total = $query->count();

        $bar = $this->output->createProgressBar($total);

        $query->chunkById(100, function ($items) use ($search, $index, $bar) {
            $documents = $items->map(fn($item) => $item->toSearchArray())->toArray();
            
            if ($this->option('queue')) {
                dispatch(new \App\Jobs\IndexDocuments($index, $documents));
            } else {
                $search->bulk($index, $documents);
            }

            $bar->advance($items->count());
        });

        $bar->finish();
        $this->newLine();
    }

    private function getModelClass(string $index): string
    {
        return match($index) {
            'articles' => \Modules\Content\Domain\Models\Article::class,
            'products' => \Modules\Ecommerce\Domain\Models\Product::class,
            'pages' => \Modules\Content\Domain\Models\Page::class,
            'services' => \Modules\Content\Domain\Models\Service::class,
            default => throw new \InvalidArgumentException("Unknown index: {$index}"),
        };
    }
}
```

---

# 18. Example File/Folder Structure

```
cms/
├── app/
│   ├── Console/Commands/
│   │   ├── ModuleMake.php
│   │   ├── ModuleMigrate.php
│   │   ├── ModuleUninstall.php
│   │   ├── ProfileSeed.php
│   │   ├── CleanupOrphanMedia.php
│   │   ├── DatabaseMaintenance.php
│   │   └── SearchReindex.php
│   ├── Contracts/
│   │   ├── SearchEngineContract.php
│   │   ├── CacheContract.php
│   │   ├── ExchangeRateProviderContract.php
│   │   └── PaymentGatewayContract.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── HealthController.php
│   │   ├── Middleware/
│   │   │   ├── ApiDeprecationMiddleware.php
│   │   │   ├── LocaleMiddleware.php
│   │   │   ├── SanitizeInput.php
│   │   │   └── TenantMiddleware.php
│   │   └── Resources/
│   │       └── ApiResponse.php
│   ├── Models/
│   │   └── User.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── ModuleServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Services/
│   │   ├── FeatureManager.php
│   │   ├── HookManager.php
│   │   ├── ProfileLoader.php
│   │   ├── CurrencyConverter.php
│   │   ├── LocaleResolver.php
│   │   └── WebhookDispatcher.php
│   └── Traits/
│       ├── HasTranslations.php
│       ├── HasRevisions.php
│       ├── HasMedia.php
│       └── Hookable.php
├── bootstrap/
│   ├── app.php
│   ├── modules.php
│   └── providers.php
├── config/
│   ├── features.php
│   ├── modules.php
│   ├── localization.php
│   ├── currencies.php
│   └── profiles/
│       ├── default.yaml
│       ├── blog.yaml
│       ├── ecommerce.yaml
│       └── corporate.yaml
├── modules/
│   ├── Core/
│   │   ├── Config/
│   │   ├── Contracts/
│   │   ├── Providers/
│   │   ├── Services/
│   │   └── module.json
│   ├── Content/
│   │   ├── Config/
│   │   ├── ContentTypes/
│   │   │   ├── article.yaml
│   │   │   ├── page.yaml
│   │   │   └── service.yaml
│   │   ├── Contracts/
│   │   ├── Database/
│   │   │   ├── Factories/
│   │   │   ├── Migrations/
│   │   │   └── Seeders/
│   │   ├── Domain/
│   │   │   ├── Models/
│   │   │   ├── DTOs/
│   │   │   └── Enums/
│   │   ├── Events/
│   │   ├── Http/
│   │   ├── Jobs/
│   │   ├── Listeners/
│   │   ├── Policies/
│   │   ├── Providers/
│   │   ├── Repositories/
│   │   ├── Routes/
│   │   ├── Services/
│   │   ├── Tests/
│   │   ├── Views/
│   │   └── module.json
│   ├── Media/
│   ├── Users/
│   ├── Auth/
│   ├── Taxonomy/
│   ├── Localization/
│   ├── Currency/
│   ├── Menu/
│   ├── Search/
│   ├── Forms/
│   ├── Comments/
│   ├── Ecommerce/
│   ├── Pricing/
│   ├── Events/
│   ├── Notifications/
│   ├── Analytics/
│   └── Themes/
├── stubs/
│   └── module/
│       ├── service-provider.stub
│       ├── model.stub
│       ├── controller.stub
│       ├── request.stub
│       ├── resource.stub
│       └── migration.stub
├── tests/
├── docker/
├── .github/workflows/
├── composer.json
├── package.json
└── README.md
```

---

# 19. Example Client Profile (YAML)

```yaml
# config/profiles/corporate-client.yaml

# ═══════════════════════════════════════════════════════════════
# Client Profile: Corporate Website
# ═══════════════════════════════════════════════════════════════

client:
  name: "Example Corporation"
  code: "example-corp"
  type: "corporate"
  environment: production

# ═══════════════════════════════════════════════════════════════
# Enabled Modules
# ═══════════════════════════════════════════════════════════════
modules:
  enabled:
    - Core
    - Users
    - Auth
    - Media
    - Localization
    - Currency
    - Taxonomy
    - Content
    - Menu
    - Search
    - Forms
    - Analytics
    - Notifications
  
  disabled:
    - Ecommerce
    - Events
    - Pricing
    - Comments

# ═══════════════════════════════════════════════════════════════
# Feature Flags
# ═══════════════════════════════════════════════════════════════
features:
  content:
    articles: true
    pages: true
    services: true
    projects: true
    testimonials: true
    revisions: true
    workflow: true
    scheduled_publishing: true
  
  media:
    image_optimization: true
    video_upload: false
    max_upload_size: 10485760
    allowed_types:
      - image/jpeg
      - image/png
      - image/webp
      - application/pdf
  
  search:
    enabled: true
    driver: meilisearch
    indexes:
      - articles
      - pages
      - services
  
  forms:
    enabled: true
    captcha: true
    spam_protection: true
    max_submissions_per_ip: 5

# ═══════════════════════════════════════════════════════════════
# Localization
# ═══════════════════════════════════════════════════════════════
localization:
  default_locale: "ar"
  fallback_locale: "en"
  
  locales:
    - code: "ar"
      name: "العربية"
      native_name: "العربية"
      direction: "rtl"
      flag: "sa"
      enabled: true
      default: true
    
    - code: "en"
      name: "English"
      native_name: "English"
      direction: "ltr"
      flag: "us"
      enabled: true

# ═══════════════════════════════════════════════════════════════
# Currency
# ═══════════════════════════════════════════════════════════════
currency:
  default: "SAR"
  display_format: "symbol_amount"
  
  currencies:
    - code: "SAR"
      name: "Saudi Riyal"
      symbol: "ر.س"
      decimals: 2
      enabled: true
      default: true
    
    - code: "USD"
      name: "US Dollar"
      symbol: "$"
      decimals: 2
      enabled: true
  
  exchange_rates:
    sync_enabled: true
    provider: "openexchangerates"
    sync_frequency: "hourly"

# ═══════════════════════════════════════════════════════════════
# Initial Data (Seeds)
# ═══════════════════════════════════════════════════════════════
seeds:
  users:
    - email: "admin@example-corp.com"
      name: "المدير"
      role: "super_admin"
    
    - email: "editor@example-corp.com"
      name: "المحرر"
      role: "editor"
  
  roles:
    - slug: "editor"
      name: "محرر"
      permissions:
        - "content.view"
        - "content.create"
        - "content.update"
        - "media.upload"
    
    - slug: "author"
      name: "كاتب"
      permissions:
        - "articles.view"
        - "articles.create"
        - "articles.update_own"
  
  taxonomy_types:
    - slug: "category"
      name: { ar: "التصنيفات", en: "Categories" }
      hierarchical: true
      applies_to: ["articles", "services"]
    
    - slug: "tag"
      name: { ar: "الوسوم", en: "Tags" }
      hierarchical: false
      applies_to: ["articles"]
    
    - slug: "industry"
      name: { ar: "القطاعات", en: "Industries" }
      hierarchical: false
      applies_to: ["projects", "services"]
  
  taxonomies:
    category:
      - slug: "news"
        name: { ar: "أخبار", en: "News" }
      - slug: "insights"
        name: { ar: "رؤى", en: "Insights" }
    
    industry:
      - slug: "technology"
        name: { ar: "التقنية", en: "Technology" }
      - slug: "finance"
        name: { ar: "المالية", en: "Finance" }
      - slug: "healthcare"
        name: { ar: "الرعاية الصحية", en: "Healthcare" }
  
  pages:
    - slug: "home"
      template: "home"
      title: { ar: "الرئيسية", en: "Home" }
      is_homepage: true
    
    - slug: "about"
      template: "about"
      title: { ar: "عن الشركة", en: "About Us" }
    
    - slug: "contact"
      template: "contact"
      title: { ar: "اتصل بنا", en: "Contact Us" }
  
  menus:
    - slug: "main-navigation"
      location: "header"
      items:
        - type: "page"
          page_slug: "home"
          order: 1
        - type: "page"
          page_slug: "about"
          order: 2
        - type: "taxonomy"
          taxonomy_type: "category"
          label: { ar: "المدونة", en: "Blog" }
          order: 3
        - type: "page"
          page_slug: "contact"
          order: 4
    
    - slug: "footer-navigation"
      location: "footer"
      items:
        - type: "custom"
          url: "/privacy"
          label: { ar: "سياسة الخصوصية", en: "Privacy Policy" }
        - type: "custom"
          url: "/terms"
          label: { ar: "الشروط والأحكام", en: "Terms of Service" }
  
  forms:
    - slug: "contact-form"
      type: "contact"
      notification_emails: "info@example-corp.com"
      fields:
        - name: "name"
          type: "text"
          required: true
          label: { ar: "الاسم", en: "Name" }
        - name: "email"
          type: "email"
          required: true
          label: { ar: "البريد الإلكتروني", en: "Email" }
        - name: "message"
          type: "textarea"
          required: true
          label: { ar: "الرسالة", en: "Message" }

# ═══════════════════════════════════════════════════════════════
# Theme Configuration
# ═══════════════════════════════════════════════════════════════
theme:
  name: "corporate"
  
  colors:
    primary: "#1a56db"
    secondary: "#374151"
    accent: "#10b981"
  
  fonts:
    heading: "Cairo"
    body: "Tajawal"
  
  logo:
    light: "assets/logo-light.svg"
    dark: "assets/logo-dark.svg"

# ═══════════════════════════════════════════════════════════════
# API Configuration
# ═══════════════════════════════════════════════════════════════
api:
  enabled: true
  versions: ["v1"]
  rate_limit: 60
  cors_origins:
    - "https://example-corp.com"
    - "https://admin.example-corp.com"

# ═══════════════════════════════════════════════════════════════
# Notifications
# ═══════════════════════════════════════════════════════════════
notifications:
  mail:
    from_address: "noreply@example-corp.com"
    from_name: "Example Corporation"
  
  channels:
    - email
    - database
```

---

**نهاية الجزء الخامس - يتبع الجزء السادس والأخير: Checklists, Commands, Pitfalls**
