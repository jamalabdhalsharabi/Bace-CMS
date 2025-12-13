<?php

declare(strict_types=1);

namespace Modules\Currency\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Currency\Application\Actions\CreateCurrencyAction;
use Modules\Currency\Application\Actions\DeleteCurrencyAction;
use Modules\Currency\Application\Actions\UpdateCurrencyAction;
use Modules\Currency\Application\Actions\UpdateExchangeRateAction;
use Modules\Currency\Application\Services\CurrencyCommandService;
use Modules\Currency\Application\Services\CurrencyQueryService;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

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
        $this->app->singleton(CurrencyRepository::class, fn ($app) => 
            new CurrencyRepository(new Currency())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateCurrencyAction::class);
        $this->app->singleton(UpdateCurrencyAction::class);
        $this->app->singleton(DeleteCurrencyAction::class);
        $this->app->singleton(UpdateExchangeRateAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(CurrencyQueryService::class);
        $this->app->singleton(CurrencyCommandService::class);
    }
}
