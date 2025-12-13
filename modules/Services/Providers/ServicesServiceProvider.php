<?php

declare(strict_types=1);

namespace Modules\Services\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Services\Application\Actions\CreateServiceAction;
use Modules\Services\Application\Actions\DeleteServiceAction;
use Modules\Services\Application\Actions\DuplicateServiceAction;
use Modules\Services\Application\Actions\PublishServiceAction;
use Modules\Services\Application\Actions\ReorderServiceAction;
use Modules\Services\Application\Actions\UpdateServiceAction;
use Modules\Services\Application\Services\ServiceCommandService;
use Modules\Services\Application\Services\ServiceQueryService;
use Modules\Services\Domain\Models\Service;
use Modules\Services\Domain\Repositories\ServiceRepository;

class ServicesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Services';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'services');

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
        $this->app->singleton(ServiceRepository::class, fn ($app) => 
            new ServiceRepository(new Service())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateServiceAction::class);
        $this->app->singleton(UpdateServiceAction::class);
        $this->app->singleton(DeleteServiceAction::class);
        $this->app->singleton(PublishServiceAction::class);
        $this->app->singleton(DuplicateServiceAction::class);
        $this->app->singleton(ReorderServiceAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ServiceQueryService::class);
        $this->app->singleton(ServiceCommandService::class);
    }
}
