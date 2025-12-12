<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make 
                            {name : The name of the module}
                            {--with-all : Generate all module components}
                            {--with-migration : Generate migration files}
                            {--with-seeder : Generate seeder files}
                            {--with-factory : Generate factory files}
                            {--with-controller : Generate controller}
                            {--with-model : Generate model}
                            {--with-service : Generate service class}
                            {--with-repository : Generate repository class}
                            {--force : Overwrite existing module}';

    protected $description = 'Create a new module';

    protected string $modulePath;
    protected string $moduleName;
    protected string $moduleNameLower;

    public function handle(): int
    {
        $this->moduleName = Str::studly($this->argument('name'));
        $this->moduleNameLower = Str::lower($this->moduleName);
        $this->modulePath = base_path('modules/' . $this->moduleName);

        if (File::isDirectory($this->modulePath) && !$this->option('force')) {
            $this->error("Module [{$this->moduleName}] already exists!");
            return self::FAILURE;
        }

        $this->info("Creating module: {$this->moduleName}");

        // Create directory structure
        $this->createDirectories();

        // Create base files
        $this->createModuleJson();
        $this->createServiceProvider();
        $this->createConfig();
        $this->createRoutes();

        // Optional components
        if ($this->option('with-all') || $this->option('with-model')) {
            $this->createModel();
        }

        if ($this->option('with-all') || $this->option('with-controller')) {
            $this->createController();
        }

        if ($this->option('with-all') || $this->option('with-service')) {
            $this->createService();
        }

        if ($this->option('with-all') || $this->option('with-repository')) {
            $this->createRepository();
        }

        if ($this->option('with-all') || $this->option('with-migration')) {
            $this->createMigration();
        }

        if ($this->option('with-all') || $this->option('with-seeder')) {
            $this->createSeeder();
        }

        if ($this->option('with-all') || $this->option('with-factory')) {
            $this->createFactory();
        }

        $this->info("Module [{$this->moduleName}] created successfully!");
        $this->newLine();
        $this->line("Path: modules/{$this->moduleName}");

        return self::SUCCESS;
    }

    protected function createDirectories(): void
    {
        $directories = [
            'Config',
            'Console/Commands',
            'Contracts',
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Domain/Models',
            'Events',
            'Exceptions',
            'Http/Controllers/Api',
            'Http/Controllers/Admin',
            'Http/Middleware',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Listeners',
            'Policies',
            'Providers',
            'Repositories',
            'Resources/lang/en',
            'Resources/lang/ar',
            'Resources/views',
            'Routes',
            'Services',
            'Tests/Feature',
            'Tests/Unit',
            'Traits',
        ];

        foreach ($directories as $directory) {
            File::makeDirectory(
                $this->modulePath . '/' . $directory,
                0755,
                true,
                true
            );
        }

        $this->info('  ✓ Directory structure created');
    }

    protected function createModuleJson(): void
    {
        $content = json_encode([
            'name' => $this->moduleName,
            'alias' => $this->moduleNameLower,
            'description' => "{$this->moduleName} module",
            'version' => '1.0.0',
            'keywords' => [],
            'priority' => 100,
            'enabled' => true,
            'providers' => [
                "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider",
            ],
            'aliases' => [],
            'files' => [],
            'dependencies' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        File::put($this->modulePath . '/module.json', $content);
        $this->info('  ✓ module.json created');
    }

    protected function createServiceProvider(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Providers;

use Illuminate\Support\ServiceProvider;

class {$this->moduleName}ServiceProvider extends ServiceProvider
{
    protected string \$moduleName = '{$this->moduleName}';
    protected string \$moduleNameLower = '{$this->moduleNameLower}';

    public function register(): void
    {
        \$this->mergeConfigFrom(
            module_path(\$this->moduleName, 'Config/config.php'),
            \$this->moduleNameLower
        );
    }

    public function boot(): void
    {
        \$this->registerViews();
        \$this->registerTranslations();
        \$this->loadMigrationsFrom(module_path(\$this->moduleName, 'Database/Migrations'));
    }

    protected function registerViews(): void
    {
        \$viewPath = resource_path('views/modules/' . \$this->moduleNameLower);
        \$sourcePath = module_path(\$this->moduleName, 'Resources/views');

        \$this->publishes([
            \$sourcePath => \$viewPath,
        ], ['views', \$this->moduleNameLower . '-views']);

        \$this->loadViewsFrom(array_merge(\$this->getPublishableViewPaths(), [\$sourcePath]), \$this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        \$langPath = resource_path('lang/modules/' . \$this->moduleNameLower);

        if (is_dir(\$langPath)) {
            \$this->loadTranslationsFrom(\$langPath, \$this->moduleNameLower);
        } else {
            \$this->loadTranslationsFrom(module_path(\$this->moduleName, 'Resources/lang'), \$this->moduleNameLower);
        }
    }

    private function getPublishableViewPaths(): array
    {
        \$paths = [];
        foreach (config('view.paths') as \$path) {
            if (is_dir(\$path . '/modules/' . \$this->moduleNameLower)) {
                \$paths[] = \$path . '/modules/' . \$this->moduleNameLower;
            }
        }
        return \$paths;
    }
}
PHP;

        File::put(
            $this->modulePath . "/Providers/{$this->moduleName}ServiceProvider.php",
            $content
        );
        $this->info('  ✓ ServiceProvider created');
    }

    protected function createConfig(): void
    {
        $content = <<<PHP
<?php

return [
    'name' => '{$this->moduleName}',
    
    // Add your module configuration here
];
PHP;

        File::put($this->modulePath . '/Config/config.php', $content);
        $this->info('  ✓ Config created');
    }

    protected function createRoutes(): void
    {
        // Web routes
        $webRoutes = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('{$this->moduleNameLower}')->name('{$this->moduleNameLower}.')->group(function () {
    // Add your web routes here
});
PHP;

        // API routes
        $apiRoutes = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/{$this->moduleNameLower}')->name('api.v1.{$this->moduleNameLower}.')->middleware(['api'])->group(function () {
    // Add your API routes here
});
PHP;

        // Admin routes
        $adminRoutes = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/{$this->moduleNameLower}')->name('admin.{$this->moduleNameLower}.')->middleware(['web', 'auth'])->group(function () {
    // Add your admin routes here
});
PHP;

        File::put($this->modulePath . '/Routes/web.php', $webRoutes);
        File::put($this->modulePath . '/Routes/api.php', $apiRoutes);
        File::put($this->modulePath . '/Routes/admin.php', $adminRoutes);
        $this->info('  ✓ Routes created');
    }

    protected function createModel(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Domain\\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$this->moduleName} extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected \$table = '{$this->moduleNameLower}s';

    protected \$fillable = [
        //
    ];

    protected \$casts = [
        //
    ];
}
PHP;

        File::put(
            $this->modulePath . "/Domain/Models/{$this->moduleName}.php",
            $content
        );
        $this->info('  ✓ Model created');
    }

    protected function createController(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$this->moduleName}Controller extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => '{$this->moduleName} module',
        ]);
    }

    public function show(string \$id): JsonResponse
    {
        return response()->json([
            'id' => \$id,
        ]);
    }

    public function store(Request \$request): JsonResponse
    {
        return response()->json([
            'message' => 'Created successfully',
        ], 201);
    }

    public function update(Request \$request, string \$id): JsonResponse
    {
        return response()->json([
            'message' => 'Updated successfully',
        ]);
    }

    public function destroy(string \$id): JsonResponse
    {
        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }
}
PHP;

        File::put(
            $this->modulePath . "/Http/Controllers/Api/{$this->moduleName}Controller.php",
            $content
        );
        $this->info('  ✓ Controller created');
    }

    protected function createService(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Services;

use Modules\\{$this->moduleName}\\Domain\\Models\\{$this->moduleName};

class {$this->moduleName}Service
{
    public function __construct()
    {
        //
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return {$this->moduleName}::all();
    }

    public function find(string \$id): ?{$this->moduleName}
    {
        return {$this->moduleName}::find(\$id);
    }

    public function create(array \$data): {$this->moduleName}
    {
        return {$this->moduleName}::create(\$data);
    }

    public function update({$this->moduleName} \$model, array \$data): {$this->moduleName}
    {
        \$model->update(\$data);
        return \$model->fresh();
    }

    public function delete({$this->moduleName} \$model): bool
    {
        return \$model->delete();
    }
}
PHP;

        File::put(
            $this->modulePath . "/Services/{$this->moduleName}Service.php",
            $content
        );
        $this->info('  ✓ Service created');
    }

    protected function createRepository(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\\{$this->moduleName}\\Domain\\Models\\{$this->moduleName};

class {$this->moduleName}Repository
{
    public function __construct(
        protected {$this->moduleName} \$model
    ) {}

    public function all(): Collection
    {
        return \$this->model->all();
    }

    public function paginate(int \$perPage = 15): LengthAwarePaginator
    {
        return \$this->model->paginate(\$perPage);
    }

    public function find(string \$id): ?{$this->moduleName}
    {
        return \$this->model->find(\$id);
    }

    public function findOrFail(string \$id): {$this->moduleName}
    {
        return \$this->model->findOrFail(\$id);
    }

    public function create(array \$data): {$this->moduleName}
    {
        return \$this->model->create(\$data);
    }

    public function update({$this->moduleName} \$entity, array \$data): {$this->moduleName}
    {
        \$entity->update(\$data);
        return \$entity->fresh();
    }

    public function delete({$this->moduleName} \$entity): bool
    {
        return \$entity->delete();
    }
}
PHP;

        File::put(
            $this->modulePath . "/Repositories/{$this->moduleName}Repository.php",
            $content
        );
        $this->info('  ✓ Repository created');
    }

    protected function createMigration(): void
    {
        $tableName = Str::snake(Str::pluralStudly($this->moduleName));
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            // Add your columns here
            \$table->boolean('is_active')->default(true);
            \$table->integer('ordering')->default(0);
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        File::put(
            $this->modulePath . "/Database/Migrations/{$fileName}",
            $content
        );
        $this->info('  ✓ Migration created');
    }

    protected function createSeeder(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Database\\Seeders;

use Illuminate\Database\Seeder;

class {$this->moduleName}Seeder extends Seeder
{
    public function run(): void
    {
        // Add your seeding logic here
    }
}
PHP;

        File::put(
            $this->modulePath . "/Database/Seeders/{$this->moduleName}Seeder.php",
            $content
        );
        $this->info('  ✓ Seeder created');
    }

    protected function createFactory(): void
    {
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$this->moduleName}\\Database\\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\\{$this->moduleName}\\Domain\\Models\\{$this->moduleName};

class {$this->moduleName}Factory extends Factory
{
    protected \$model = {$this->moduleName}::class;

    public function definition(): array
    {
        return [
            'is_active' => true,
            'ordering' => 0,
        ];
    }
}
PHP;

        File::put(
            $this->modulePath . "/Database/Factories/{$this->moduleName}Factory.php",
            $content
        );
        $this->info('  ✓ Factory created');
    }
}
