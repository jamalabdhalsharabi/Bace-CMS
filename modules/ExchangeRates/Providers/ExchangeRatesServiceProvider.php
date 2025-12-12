<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ExchangeRates\Contracts\ExchangeRateServiceContract;
use Modules\ExchangeRates\Services\ExchangeRateService;

class ExchangeRatesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ExchangeRates';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'exchange-rates');
        $this->app->bind(ExchangeRateServiceContract::class, ExchangeRateService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
