<?php

declare(strict_types=1);

namespace Modules\Currency\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Currency\Contracts\CurrencyServiceContract;
use Modules\Currency\Services\CurrencyService;

class CurrencyServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Currency';
    protected string $moduleNameLower = 'currency';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(CurrencyServiceContract::class, CurrencyService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
