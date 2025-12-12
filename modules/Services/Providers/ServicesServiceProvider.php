<?php

declare(strict_types=1);

namespace Modules\Services\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Services\Contracts\ServiceServiceContract;
use Modules\Services\Services\ServiceService;

class ServicesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Services';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'services');
        $this->app->bind(ServiceServiceContract::class, ServiceService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
