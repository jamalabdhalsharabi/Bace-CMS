<?php

declare(strict_types=1);

namespace Modules\Events\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Events\Application\Actions\Event\CreateEventAction;
use Modules\Events\Application\Actions\Event\DeleteEventAction;
use Modules\Events\Application\Actions\Event\DuplicateEventAction;
use Modules\Events\Application\Actions\Event\PublishEventAction;
use Modules\Events\Application\Actions\Event\UpdateEventAction;
use Modules\Events\Application\Actions\Registration\CancelRegistrationAction;
use Modules\Events\Application\Actions\Registration\ConfirmRegistrationAction;
use Modules\Events\Application\Actions\Registration\CreateRegistrationAction;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Application\Services\EventRegistrationService;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

/**
 * Events Module Service Provider.
 */
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
        $this->app->singleton(EventRepository::class, fn ($app) => 
            new EventRepository(new Event())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateEventAction::class);
        $this->app->singleton(UpdateEventAction::class);
        $this->app->singleton(PublishEventAction::class);
        $this->app->singleton(DeleteEventAction::class);
        $this->app->singleton(DuplicateEventAction::class);
        $this->app->singleton(CreateRegistrationAction::class);
        $this->app->singleton(ConfirmRegistrationAction::class);
        $this->app->singleton(CancelRegistrationAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(EventQueryService::class);
        $this->app->singleton(EventCommandService::class);
        $this->app->singleton(EventRegistrationService::class);
    }
}
