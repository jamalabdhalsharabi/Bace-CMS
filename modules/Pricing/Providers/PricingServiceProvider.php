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
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

class PricingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Pricing';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'pricing');

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
        $this->app->singleton(PlanRepository::class, fn ($app) => 
            new PlanRepository(new Plan())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreatePlanAction::class);
        $this->app->singleton(UpdatePlanAction::class);
        $this->app->singleton(DeletePlanAction::class);
        $this->app->singleton(TogglePlanAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(PlanQueryService::class);
        $this->app->singleton(PlanCommandService::class);
    }
}