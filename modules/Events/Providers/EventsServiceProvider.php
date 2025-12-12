<?php

declare(strict_types=1);

namespace Modules\Events\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Events\Contracts\EventServiceContract;
use Modules\Events\Services\EventService;

class EventsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Events';
    protected string $moduleNameLower = 'events';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
        $this->app->bind(EventServiceContract::class, EventService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
