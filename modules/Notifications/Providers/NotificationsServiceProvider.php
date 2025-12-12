<?php

declare(strict_types=1);

namespace Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Contracts\NotificationServiceContract;
use Modules\Notifications\Services\NotificationService;

class NotificationsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Notifications';
    protected string $moduleNameLower = 'notifications';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(NotificationServiceContract::class, NotificationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
