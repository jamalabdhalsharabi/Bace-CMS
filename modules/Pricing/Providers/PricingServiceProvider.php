<?php

declare(strict_types=1);

namespace Modules\Pricing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Contracts\PlanServiceContract;
use Modules\Pricing\Contracts\SubscriptionServiceContract;
use Modules\Pricing\Contracts\CouponServiceContract;
use Modules\Pricing\Services\PlanService;
use Modules\Pricing\Services\SubscriptionService;
use Modules\Pricing\Services\CouponService;

class PricingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Pricing';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'pricing');

        $this->app->bind(PlanServiceContract::class, PlanService::class);
        $this->app->bind(SubscriptionServiceContract::class, SubscriptionService::class);
        $this->app->bind(CouponServiceContract::class, CouponService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}