<?php

declare(strict_types=1);

namespace Modules\Projects\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Projects\Contracts\ProjectServiceContract;
use Modules\Projects\Services\ProjectService;

class ProjectsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Projects';
    protected string $moduleNameLower = 'projects';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(ProjectServiceContract::class, ProjectService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
