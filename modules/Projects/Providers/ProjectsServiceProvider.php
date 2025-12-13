<?php

declare(strict_types=1);

namespace Modules\Projects\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Projects\Application\Actions\CreateProjectAction;
use Modules\Projects\Application\Actions\DeleteProjectAction;
use Modules\Projects\Application\Actions\DuplicateProjectAction;
use Modules\Projects\Application\Actions\FeatureProjectAction;
use Modules\Projects\Application\Actions\PublishProjectAction;
use Modules\Projects\Application\Actions\UpdateProjectAction;
use Modules\Projects\Application\Services\ProjectCommandService;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

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

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(ProjectRepository::class, fn ($app) => 
            new ProjectRepository(new Project())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateProjectAction::class);
        $this->app->singleton(UpdateProjectAction::class);
        $this->app->singleton(DeleteProjectAction::class);
        $this->app->singleton(PublishProjectAction::class);
        $this->app->singleton(DuplicateProjectAction::class);
        $this->app->singleton(FeatureProjectAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(ProjectQueryService::class);
        $this->app->singleton(ProjectCommandService::class);
    }
}
