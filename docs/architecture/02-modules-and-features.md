# دليل هيكلة CMS احترافي - Laravel 12
## الجزء الثاني: نظام Modules وFeature Toggles

---

# 4. نظام تفعيل وتعطيل الميزات

## 4.1 آلية تسجيل Module

### Module Loader (bootstrap/modules.php)
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Loading Order (priority)
    |--------------------------------------------------------------------------
    */
    'enabled' => [
        'Core'          => ['priority' => 1,  'required' => true],
        'Users'         => ['priority' => 5,  'required' => true],
        'Auth'          => ['priority' => 10, 'required' => true],
        'Media'         => ['priority' => 15, 'required' => false],
        'Localization'  => ['priority' => 20, 'required' => false],
        'Currency'      => ['priority' => 25, 'required' => false],
        'Taxonomy'      => ['priority' => 30, 'required' => false],
        'Content'       => ['priority' => 35, 'required' => false],
        'Menu'          => ['priority' => 40, 'required' => false],
        'Forms'         => ['priority' => 45, 'required' => false],
        'Comments'      => ['priority' => 50, 'required' => false],
        'Search'        => ['priority' => 55, 'required' => false],
        'Ecommerce'     => ['priority' => 60, 'required' => false],
        'Pricing'       => ['priority' => 65, 'required' => false],
        'Events'        => ['priority' => 70, 'required' => false],
        'Notifications' => ['priority' => 75, 'required' => false],
        'Analytics'     => ['priority' => 80, 'required' => false],
        'Themes'        => ['priority' => 99, 'required' => false],
    ],

    'disabled' => [
        // Modules explicitly disabled
    ],
];
```

### Feature Manager Service
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class FeatureManager
{
    private array $features;
    private array $modules;

    public function __construct()
    {
        $this->features = config('features', []);
        $this->modules = config('modules.enabled', []);
    }

    public function enabled(string $feature): bool
    {
        // Check environment override first
        $envKey = 'FEATURE_' . strtoupper(str_replace('.', '_', $feature));
        if (env($envKey) !== null) {
            return (bool) env($envKey);
        }

        // Check cached features (for runtime toggles)
        $cached = Cache::get("feature:{$feature}");
        if ($cached !== null) {
            return $cached;
        }

        // Check config
        return data_get($this->features, $feature, false);
    }

    public function moduleEnabled(string $module): bool
    {
        return isset($this->modules[$module]);
    }

    public function enable(string $feature): void
    {
        Cache::forever("feature:{$feature}", true);
    }

    public function disable(string $feature): void
    {
        Cache::forever("feature:{$feature}", false);
    }

    public function all(): array
    {
        return $this->features;
    }

    public function enabledModules(): array
    {
        return array_keys($this->modules);
    }
}
```

### Service Provider Registration
```php
<?php
// app/Providers/ModuleServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerFeatureManager();
        $this->registerModules();
    }

    protected function registerFeatureManager(): void
    {
        $this->app->singleton('features', fn() => new \App\Services\FeatureManager());
    }

    protected function registerModules(): void
    {
        $modules = config('modules.enabled', []);
        
        // Sort by priority
        uasort($modules, fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach (array_keys($modules) as $module) {
            $this->registerModule($module);
        }
    }

    protected function registerModule(string $module): void
    {
        $providerClass = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";
        
        if (class_exists($providerClass)) {
            $this->app->register($providerClass);
        }
    }
}
```

## 4.2 Hooks & Events API

### Plugin Hook System
```php
<?php

namespace App\Services;

final class HookManager
{
    private array $actions = [];
    private array $filters = [];

    /**
     * Register an action hook
     */
    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    /**
     * Execute action hooks
     */
    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);
        
        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback(...$args);
            }
        }
    }

    /**
     * Register a filter hook
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    /**
     * Apply filter hooks
     */
    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $callback($value, ...$args);
            }
        }

        return $value;
    }

    /**
     * Remove all hooks for a module (on uninstall)
     */
    public function removeModuleHooks(string $modulePrefix): void
    {
        // Implementation to remove hooks by prefix
    }
}
```

### Available Hooks
```php
<?php
// Hook definitions

// Content hooks
'content.before_save'           // Before saving any content
'content.after_save'            // After saving any content
'content.before_publish'        // Before publishing
'content.after_publish'         // After publishing
'content.before_delete'         // Before deletion
'content.after_delete'          // After deletion

// Media hooks
'media.before_upload'           // Before file upload
'media.after_upload'            // After file upload
'media.before_process'          // Before image processing
'media.after_process'           // After image processing

// User hooks
'user.before_register'          // Before user registration
'user.after_register'           // After user registration
'user.before_login'             // Before login
'user.after_login'              // After successful login

// Ecommerce hooks
'cart.before_add'               // Before adding to cart
'cart.after_add'                // After adding to cart
'order.before_create'           // Before order creation
'order.after_create'            // After order creation
'order.status_changed'          // When order status changes

// API hooks
'api.response.before'           // Before API response
'api.response.after'            // After API response

// Admin hooks
'admin.menu.register'           // Register admin menu items
'admin.dashboard.widgets'       // Register dashboard widgets
```

### Using Hooks in Module
```php
<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Support\ServiceProvider;
use App\Facades\Hook;

final class EcommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register hooks
        Hook::addAction('content.after_publish', [$this, 'syncProductToChannels'], 20);
        Hook::addFilter('admin.menu.register', [$this, 'addEcommerceMenu'], 10);
    }

    public function syncProductToChannels($content): void
    {
        if ($content instanceof Product) {
            dispatch(new SyncProductToChannels($content));
        }
    }

    public function addEcommerceMenu(array $menu): array
    {
        $menu['ecommerce'] = [
            'label' => 'E-commerce',
            'icon' => 'shopping-cart',
            'children' => [
                'products' => ['label' => 'Products', 'route' => 'admin.products.index'],
                'orders' => ['label' => 'Orders', 'route' => 'admin.orders.index'],
            ],
        ];
        return $menu;
    }
}
```

## 4.3 Lazy Loading Modules

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class DeferredModuleServiceProvider extends ServiceProvider
{
    /**
     * Modules to load only when needed
     */
    protected array $deferredModules = [
        'Ecommerce' => [
            'routes' => ['shop.*', 'cart.*', 'checkout.*'],
            'commands' => ['ecommerce:*'],
        ],
        'Events' => [
            'routes' => ['events.*'],
            'commands' => ['events:*'],
        ],
    ];

    public function register(): void
    {
        foreach ($this->deferredModules as $module => $triggers) {
            $this->registerDeferredModule($module, $triggers);
        }
    }

    protected function registerDeferredModule(string $module, array $triggers): void
    {
        // Only load if route matches
        if ($this->routeMatches($triggers['routes'] ?? [])) {
            $this->loadModule($module);
            return;
        }

        // Only load if command matches
        if ($this->app->runningInConsole() && $this->commandMatches($triggers['commands'] ?? [])) {
            $this->loadModule($module);
        }
    }

    protected function loadModule(string $module): void
    {
        $provider = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";
        if (class_exists($provider)) {
            $this->app->register($provider);
        }
    }
}
```

## 4.4 Module Migrations Strategy

```php
<?php
// modules/Ecommerce/Database/Migrations/2024_01_01_000001_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module identifier for tracking
     */
    public string $module = 'ecommerce';

    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('type')->default('physical');
            $table->string('status')->default('draft');
            // ...
            $table->timestamps();
            $table->softDeletes();
        });

        // Track migration for module
        DB::table('module_migrations')->insert([
            'module' => $this->module,
            'migration' => get_class($this),
            'batch' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        
        DB::table('module_migrations')
            ->where('module', $this->module)
            ->where('migration', get_class($this))
            ->delete();
    }
};
```

### Module Migration Commands
```php
<?php
// Artisan commands for module migrations

// Run migrations for specific module
// php artisan module:migrate Ecommerce

// Rollback module migrations
// php artisan module:migrate-rollback Ecommerce

// Fresh module (drop all module tables and re-migrate)
// php artisan module:migrate-fresh Ecommerce

// Status of module migrations
// php artisan module:migrate-status Ecommerce
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class ModuleMigrate extends Command
{
    protected $signature = 'module:migrate 
                            {module : The module name}
                            {--seed : Seed the module after migration}
                            {--fresh : Drop all module tables first}';

    protected $description = 'Run migrations for a specific module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $path = "modules/{$module}/Database/Migrations";

        if (!is_dir(base_path($path))) {
            $this->error("Module {$module} not found or has no migrations.");
            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->call('module:migrate-rollback', ['module' => $module]);
        }

        $this->info("Running migrations for module: {$module}");

        Artisan::call('migrate', [
            '--path' => $path,
            '--force' => true,
        ]);

        $this->info(Artisan::output());

        if ($this->option('seed')) {
            $this->call('module:seed', ['module' => $module]);
        }

        return Command::SUCCESS;
    }
}
```

## 4.5 Module Removal & Cleanup

### Uninstall Command
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

final class ModuleUninstall extends Command
{
    protected $signature = 'module:uninstall 
                            {module : The module name}
                            {--force : Skip confirmation}
                            {--keep-data : Do not drop database tables}';

    protected $description = 'Uninstall a module completely';

    public function handle(): int
    {
        $module = $this->argument('module');

        // Check if module exists
        if (!$this->moduleExists($module)) {
            $this->error("Module {$module} not found.");
            return Command::FAILURE;
        }

        // Check dependencies
        $dependents = $this->findDependentModules($module);
        if (!empty($dependents)) {
            $this->error("Cannot uninstall {$module}. The following modules depend on it:");
            foreach ($dependents as $dep) {
                $this->line("  - {$dep}");
            }
            return Command::FAILURE;
        }

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to uninstall {$module}?")) {
                return Command::SUCCESS;
            }
        }

        $this->info("Uninstalling module: {$module}");

        // Step 1: Disable module in config
        $this->disableModule($module);
        $this->info("✓ Module disabled in config");

        // Step 2: Clear caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        $this->info("✓ Caches cleared");

        // Step 3: Run module cleanup hooks
        $this->runCleanupHooks($module);
        $this->info("✓ Cleanup hooks executed");

        // Step 4: Rollback migrations (unless --keep-data)
        if (!$this->option('keep-data')) {
            $this->call('module:migrate-rollback', ['module' => $module, '--force' => true]);
            $this->info("✓ Database tables removed");
        }

        // Step 5: Remove orphaned media
        $this->cleanupOrphanedMedia($module);
        $this->info("✓ Orphaned media cleaned");

        // Step 6: Remove from modules config
        $this->removeFromConfig($module);
        $this->info("✓ Removed from config");

        // Step 7: Optionally delete module files
        if ($this->confirm("Delete module files from disk?")) {
            File::deleteDirectory(base_path("modules/{$module}"));
            $this->info("✓ Module files deleted");
        }

        $this->info("Module {$module} uninstalled successfully!");
        return Command::SUCCESS;
    }

    protected function runCleanupHooks(string $module): void
    {
        $hookClass = "Modules\\{$module}\\Hooks\\UninstallHook";
        if (class_exists($hookClass)) {
            (new $hookClass)->handle();
        }
    }

    protected function cleanupOrphanedMedia(string $module): void
    {
        // Find media only referenced by this module's content
        // and either delete or reassign
    }
}
```

### Orphan Detection Service
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class OrphanDetector
{
    /**
     * Find orphaned records after module removal
     */
    public function findOrphans(): array
    {
        $orphans = [];

        // Orphaned media (not referenced by any content)
        $orphans['media'] = DB::table('media')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('content_media')
                    ->whereColumn('content_media.media_id', 'media.id');
            })
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        // Orphaned translations (missing parent)
        $translationTables = [
            'article_translations' => 'articles',
            'page_translations' => 'pages',
            'product_translations' => 'products',
        ];

        foreach ($translationTables as $transTable => $parentTable) {
            if (!Schema::hasTable($transTable) || !Schema::hasTable($parentTable)) {
                continue;
            }
            
            $fk = str_replace('_translations', '_id', $transTable);
            $orphans[$transTable] = DB::table($transTable)
                ->whereNotExists(function ($query) use ($parentTable, $fk) {
                    $query->select(DB::raw(1))
                        ->from($parentTable)
                        ->whereColumn("{$parentTable}.id", $fk);
                })
                ->count();
        }

        // Orphaned taxonomy relations
        $orphans['content_taxonomies'] = DB::table('content_taxonomies')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('taxonomies')
                    ->whereColumn('taxonomies.id', 'content_taxonomies.taxonomy_id');
            })
            ->count();

        return array_filter($orphans);
    }

    /**
     * Cleanup orphaned records
     */
    public function cleanup(bool $dryRun = true): array
    {
        $cleaned = [];

        if ($dryRun) {
            return $this->findOrphans();
        }

        // Actual cleanup logic
        // ...

        return $cleaned;
    }
}
```

---

# 5. نماذج ملفات التكوين والSeeders

## 5.1 Client Profile Configuration

```yaml
# config/profiles/client-example.yaml

# Client Profile Configuration
# Used to quickly configure a new client installation

client:
  name: "Example Company"
  code: "example-co"
  environment: production

# Enabled modules
modules:
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
  # Disabled:
  # - Ecommerce
  # - Events
  # - Forms
  # - Comments

# Feature flags
features:
  content:
    articles: true
    pages: true
    services: true
    projects: false
    revisions: true
    workflow: true
    scheduled_publishing: true
  
  media:
    image_optimization: true
    video_upload: false
    max_upload_size: 10485760  # 10MB
  
  localization:
    enabled: true
    default_locale: "ar"
    fallback_locale: "en"
    available_locales:
      - code: "ar"
        name: "العربية"
        direction: "rtl"
        default: true
      - code: "en"
        name: "English"
        direction: "ltr"
  
  currency:
    enabled: true
    default: "SAR"
    available:
      - code: "SAR"
        symbol: "ر.س"
        decimals: 2
      - code: "USD"
        symbol: "$"
        decimals: 2
    exchange_rate_sync: true
    sync_provider: "openexchangerates"

# Initial data to seed
seeds:
  users:
    - email: "admin@example.com"
      name: "Admin"
      role: "super_admin"
  
  taxonomies:
    categories:
      - slug: "services"
        name: { ar: "الخدمات", en: "Services" }
      - slug: "industries"
        name: { ar: "القطاعات", en: "Industries" }
    
    tags: []
  
  menus:
    - slug: "main-menu"
      location: "header"
      items:
        - type: "page"
          page_slug: "home"
        - type: "page"
          page_slug: "about"
        - type: "page"
          page_slug: "contact"

# Theme configuration
theme:
  name: "default"
  primary_color: "#3B82F6"
  logo: "assets/logo.svg"

# API configuration
api:
  enabled: true
  rate_limit: 60
  versions: ["v1"]
```

## 5.2 Profile Loader Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

final class ProfileLoader
{
    public function load(string $profileName): array
    {
        $path = config_path("profiles/{$profileName}.yaml");
        
        if (!File::exists($path)) {
            throw new \InvalidArgumentException("Profile {$profileName} not found");
        }

        return Yaml::parseFile($path);
    }

    public function apply(string $profileName): void
    {
        $profile = $this->load($profileName);

        // Apply modules configuration
        $this->applyModules($profile['modules'] ?? []);

        // Apply features
        $this->applyFeatures($profile['features'] ?? []);

        // Apply localization
        $this->applyLocalization($profile['features']['localization'] ?? []);

        // Apply currency
        $this->applyCurrency($profile['features']['currency'] ?? []);
    }

    protected function applyModules(array $modules): void
    {
        $configPath = base_path('bootstrap/modules.php');
        $current = require $configPath;

        // Enable only specified modules
        $enabled = [];
        foreach ($modules as $module) {
            if (isset($current['enabled'][$module])) {
                $enabled[$module] = $current['enabled'][$module];
            }
        }

        // Write updated config
        $content = "<?php\n\nreturn " . var_export(['enabled' => $enabled], true) . ";\n";
        File::put($configPath, $content);
    }

    protected function applyFeatures(array $features): void
    {
        $envContent = File::get(base_path('.env'));

        foreach ($this->flattenFeatures($features) as $key => $value) {
            $envKey = 'FEATURE_' . strtoupper(str_replace('.', '_', $key));
            $envValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;

            if (str_contains($envContent, "{$envKey}=")) {
                $envContent = preg_replace("/^{$envKey}=.*/m", "{$envKey}={$envValue}", $envContent);
            } else {
                $envContent .= "\n{$envKey}={$envValue}";
            }
        }

        File::put(base_path('.env'), $envContent);
    }

    protected function flattenFeatures(array $features, string $prefix = ''): array
    {
        $result = [];
        foreach ($features as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value) && !isset($value[0])) {
                $result = array_merge($result, $this->flattenFeatures($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }
}
```

## 5.3 Profile Seeder Command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProfileLoader;
use Illuminate\Support\Facades\Artisan;

final class SeedProfile extends Command
{
    protected $signature = 'profile:seed 
                            {profile : Profile name (without .yaml extension)}
                            {--fresh : Fresh install (drop all tables first)}';

    protected $description = 'Seed database from a client profile';

    public function __construct(
        private readonly ProfileLoader $loader,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $profileName = $this->argument('profile');

        $this->info("Loading profile: {$profileName}");

        try {
            $profile = $this->loader->load($profileName);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        }

        // Apply profile configuration
        $this->loader->apply($profileName);
        $this->info("✓ Profile configuration applied");

        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // Run seeders based on profile
        $this->seedFromProfile($profile);

        $this->info("Profile {$profileName} seeded successfully!");
        return Command::SUCCESS;
    }

    protected function seedFromProfile(array $profile): void
    {
        // Seed users
        if (!empty($profile['seeds']['users'])) {
            $this->seedUsers($profile['seeds']['users']);
            $this->info("✓ Users seeded");
        }

        // Seed taxonomies
        if (!empty($profile['seeds']['taxonomies'])) {
            $this->seedTaxonomies($profile['seeds']['taxonomies']);
            $this->info("✓ Taxonomies seeded");
        }

        // Seed languages
        if (!empty($profile['features']['localization']['available_locales'])) {
            $this->seedLanguages($profile['features']['localization']['available_locales']);
            $this->info("✓ Languages seeded");
        }

        // Seed currencies
        if (!empty($profile['features']['currency']['available'])) {
            $this->seedCurrencies($profile['features']['currency']['available']);
            $this->info("✓ Currencies seeded");
        }

        // Seed menus
        if (!empty($profile['seeds']['menus'])) {
            $this->seedMenus($profile['seeds']['menus']);
            $this->info("✓ Menus seeded");
        }
    }

    protected function seedUsers(array $users): void
    {
        foreach ($users as $userData) {
            \App\Models\User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                ]
            )->assignRole($userData['role']);
        }
    }

    // ... other seed methods
}
```

---

# 6. إستراتيجية Content Types قابلة للتخصيص

## 6.1 Content Type Definition (YAML)

```yaml
# modules/Content/ContentTypes/article.yaml

name: article
label:
  singular: { ar: "مقال", en: "Article" }
  plural: { ar: "مقالات", en: "Articles" }

icon: "file-text"
table: articles
translation_table: article_translations

# Features
features:
  translatable: true
  revisions: true
  soft_deletes: true
  media: true
  taxonomies: true
  seo: true
  comments: true
  scheduling: true

# Status workflow
statuses:
  - draft
  - pending_review
  - in_review
  - approved
  - rejected
  - scheduled
  - published
  - unpublished
  - archived

# Main table fields (non-translatable)
fields:
  - name: type
    type: enum
    options: [post, news, tutorial, review]
    default: post
    
  - name: author_id
    type: relation
    relation: belongsTo
    model: User
    required: true
    
  - name: featured_image_id
    type: relation
    relation: belongsTo
    model: Media
    
  - name: is_featured
    type: boolean
    default: false
    
  - name: is_pinned
    type: boolean
    default: false
    
  - name: allow_comments
    type: boolean
    default: true
    
  - name: view_count
    type: integer
    default: 0
    readonly: true
    
  - name: reading_time
    type: integer
    readonly: true
    computed: true

# Translatable fields
translatable_fields:
  - name: title
    type: string
    max: 255
    required: true
    
  - name: slug
    type: slug
    from: title
    unique: true
    required: true
    
  - name: excerpt
    type: text
    max: 500
    
  - name: content
    type: richtext
    required: true
    
  - name: meta_title
    type: string
    max: 255
    
  - name: meta_description
    type: text
    max: 160

# Taxonomies
taxonomies:
  - type: category
    multiple: true
    required: false
  - type: tag
    multiple: true
    required: false

# Validation rules
validation:
  create:
    title: "required|string|max:255"
    content: "required|string"
    author_id: "required|exists:users,id"
    
  update:
    title: "sometimes|string|max:255"
    content: "sometimes|string"

# API resource
api:
  list:
    fields: [id, title, slug, excerpt, featured_image, author, published_at]
    includes: [author, categories, tags]
    filters: [status, type, author_id, category, tag, date_range]
    sorts: [published_at, created_at, view_count, title]
    
  detail:
    fields: [id, title, slug, excerpt, content, featured_image, author, published_at, seo]
    includes: [author, categories, tags, related_articles]

# Admin UI
admin:
  list:
    columns: [title, author, status, published_at, view_count]
    actions: [edit, publish, unpublish, delete]
    bulk_actions: [publish, unpublish, delete, change_author]
    
  form:
    layout: two-column
    sections:
      - name: main
        fields: [title, slug, excerpt, content]
      - name: sidebar
        fields: [status, author_id, featured_image_id, is_featured, is_pinned]
      - name: taxonomies
        fields: [categories, tags]
      - name: seo
        fields: [meta_title, meta_description]
```

## 6.2 Content Type Registry

```php
<?php

namespace Modules\Content\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Collection;

final class ContentTypeRegistry
{
    private Collection $types;

    public function __construct()
    {
        $this->types = collect();
        $this->loadContentTypes();
    }

    protected function loadContentTypes(): void
    {
        $path = module_path('Content', 'ContentTypes');
        
        foreach (File::glob("{$path}/*.yaml") as $file) {
            $definition = Yaml::parseFile($file);
            $this->register($definition['name'], $definition);
        }
    }

    public function register(string $name, array $definition): void
    {
        $this->types->put($name, new ContentTypeDefinition($definition));
    }

    public function get(string $name): ?ContentTypeDefinition
    {
        return $this->types->get($name);
    }

    public function all(): Collection
    {
        return $this->types;
    }

    public function enabled(): Collection
    {
        return $this->types->filter(fn($type) => $type->isEnabled());
    }
}
```

## 6.3 Dynamic Model Generation

```php
<?php

namespace Modules\Content\Services;

use Illuminate\Database\Eloquent\Model;

final class DynamicModelFactory
{
    public function __construct(
        private readonly ContentTypeRegistry $registry,
    ) {}

    public function make(string $contentType): Model
    {
        $definition = $this->registry->get($contentType);
        
        if (!$definition) {
            throw new \InvalidArgumentException("Content type {$contentType} not found");
        }

        // Return existing model if defined
        $modelClass = $definition->getModelClass();
        if (class_exists($modelClass)) {
            return new $modelClass;
        }

        // Generate dynamic model
        return $this->generateModel($definition);
    }

    protected function generateModel(ContentTypeDefinition $definition): Model
    {
        return new class($definition) extends Model {
            use HasTranslations, HasRevisions, HasMedia, SoftDeletes;

            public function __construct(ContentTypeDefinition $definition)
            {
                parent::__construct();
                
                $this->table = $definition->getTable();
                $this->translatable = $definition->getTranslatableFields();
                $this->fillable = $definition->getAllFields();
                $this->casts = $definition->getCasts();
            }
        };
    }
}
```

## 6.4 Dynamic Form Generation

```php
<?php

namespace Modules\Content\Services;

final class FormBuilder
{
    public function buildFromContentType(ContentTypeDefinition $definition): array
    {
        $form = [
            'sections' => [],
            'validation' => $definition->getValidationRules('create'),
        ];

        foreach ($definition->getAdminFormSections() as $section) {
            $form['sections'][] = [
                'name' => $section['name'],
                'label' => __("content.sections.{$section['name']}"),
                'fields' => $this->buildFields($section['fields'], $definition),
            ];
        }

        return $form;
    }

    protected function buildFields(array $fieldNames, ContentTypeDefinition $definition): array
    {
        $fields = [];
        
        foreach ($fieldNames as $fieldName) {
            $fieldDef = $definition->getField($fieldName);
            if (!$fieldDef) continue;

            $fields[] = [
                'name' => $fieldName,
                'type' => $this->mapFieldType($fieldDef['type']),
                'label' => __("content.fields.{$fieldName}"),
                'required' => $fieldDef['required'] ?? false,
                'options' => $fieldDef['options'] ?? null,
                'default' => $fieldDef['default'] ?? null,
                'readonly' => $fieldDef['readonly'] ?? false,
                'translatable' => $definition->isTranslatable($fieldName),
            ];
        }

        return $fields;
    }

    protected function mapFieldType(string $type): string
    {
        return match($type) {
            'string' => 'text',
            'text' => 'textarea',
            'richtext' => 'editor',
            'boolean' => 'toggle',
            'enum' => 'select',
            'relation' => 'relation-picker',
            'slug' => 'slug',
            'integer' => 'number',
            default => 'text',
        };
    }
}
```

## 6.5 Content Type Migration Generator

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Content\Services\ContentTypeRegistry;

final class GenerateContentTypeMigration extends Command
{
    protected $signature = 'content-type:migration 
                            {type : Content type name}
                            {--fresh : Generate fresh migration}';

    protected $description = 'Generate migration for a content type';

    public function handle(ContentTypeRegistry $registry): int
    {
        $typeName = $this->argument('type');
        $definition = $registry->get($typeName);

        if (!$definition) {
            $this->error("Content type {$typeName} not found");
            return Command::FAILURE;
        }

        $migration = $this->generateMigration($definition);
        
        $filename = date('Y_m_d_His') . "_create_{$definition->getTable()}_table.php";
        $path = module_path('Content', "Database/Migrations/{$filename}");
        
        file_put_contents($path, $migration);
        
        $this->info("Migration created: {$filename}");
        return Command::SUCCESS;
    }

    protected function generateMigration(ContentTypeDefinition $definition): string
    {
        $table = $definition->getTable();
        $columns = $this->generateColumns($definition);
        $translationColumns = $this->generateTranslationColumns($definition);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main table
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
{$columns}
            \$table->timestamps();
            \$table->softDeletes();
        });

        // Translations table
        Schema::create('{$table}_translations', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->foreignUuid('{$definition->getForeignKey()}')->constrained('{$table}')->cascadeOnDelete();
            \$table->string('locale', 10);
{$translationColumns}
            \$table->timestamps();
            
            \$table->unique(['{$definition->getForeignKey()}', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}_translations');
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }
}
```

---

**نهاية الجزء الثاني - يتبع الجزء الثالث: Database وAPI وExtensibility**
