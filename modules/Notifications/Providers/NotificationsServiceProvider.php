<?php

declare(strict_types=1);

namespace Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Application\Actions\DeleteNotificationAction;
use Modules\Notifications\Application\Actions\MarkNotificationReadAction;
use Modules\Notifications\Application\Actions\SendNotificationAction;
use Modules\Notifications\Application\Services\NotificationCommandService;
use Modules\Notifications\Application\Services\NotificationQueryService;
use Modules\Notifications\Domain\Models\Notification;
use Modules\Notifications\Domain\Repositories\NotificationRepository;

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
        $this->app->singleton(NotificationRepository::class, fn ($app) => 
            new NotificationRepository(new Notification())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(SendNotificationAction::class);
        $this->app->singleton(MarkNotificationReadAction::class);
        $this->app->singleton(DeleteNotificationAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(NotificationQueryService::class);
        $this->app->singleton(NotificationCommandService::class);
    }
}
