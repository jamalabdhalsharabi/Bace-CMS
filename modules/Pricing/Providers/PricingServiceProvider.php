<?php

declare(strict_types=1);

namespace Modules\Pricing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Application\Actions\CreatePlanAction;
use Modules\Pricing\Application\Actions\DeletePlanAction;
use Modules\Pricing\Application\Actions\TogglePlanAction;
use Modules\Pricing\Application\Actions\UpdatePlanAction;
use Modules\Pricing\Application\Services\PlanCommandService;
use Modules\Pricing\Application\Services\PlanQueryService;
use Modules\Pricing\Domain\Contracts\PlanRepositoryInterface;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Pricing Module Service Provider.
 *
 * Registers and bootstraps the Pricing module including:
 * - Repository bindings (Interface to Implementation)
 * - Plan management actions (CRUD, Toggle)
 * - Query and Command services
 * - Migrations and API routes
 *
 * @package Modules\Pricing\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class PricingServiceProvider extends ServiceProvider
{
    /**
     * Module name for path resolution.
     *
     * @var string
     */
    protected string $moduleName = 'Pricing';

    /**
     * Register module services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'pricing');

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    /**
     * Bootstrap module services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }

    /**
     * Register repository bindings.
     *
     * Binds repository interfaces to their concrete implementations.
     *
     * @return void
     */
    protected function registerRepositories(): void
    {
        // Bind interface to implementation for dependency injection
        $this->app->bind(
            PlanRepositoryInterface::class,
            PlanRepository::class
        );

        // Register concrete repository as singleton
        $this->app->singleton(PlanRepository::class, fn ($app) => 
            new PlanRepository(new PricingPlan())
        );
    }

    /**
     * Register action classes.
     *
     * Actions are single-responsibility classes for specific operations.
     *
     * @return void
     */
    protected function registerActions(): void
    {
        $this->app->singleton(CreatePlanAction::class);
        $this->app->singleton(UpdatePlanAction::class);
        $this->app->singleton(DeletePlanAction::class);
        $this->app->singleton(TogglePlanAction::class);
    }

    /**
     * Register service classes.
     *
     * Services orchestrate actions and provide business logic.
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton(PlanQueryService::class);
        $this->app->singleton(PlanCommandService::class);
    }
}
