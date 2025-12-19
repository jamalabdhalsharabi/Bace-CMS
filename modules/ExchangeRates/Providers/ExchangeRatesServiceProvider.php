<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ExchangeRates\Application\Actions\ConvertCurrencyAction;
use Modules\ExchangeRates\Application\Actions\CreateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\DeactivateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\FetchExchangeRatesAction;
use Modules\ExchangeRates\Application\Actions\FreezeExchangeRateAction;
use Modules\ExchangeRates\Application\Actions\UpdateExchangeRateAction;
use Modules\ExchangeRates\Application\Services\ExchangeRateCommandService;
use Modules\ExchangeRates\Application\Services\ExchangeRateQueryService;
use Modules\ExchangeRates\Domain\Contracts\ExchangeRateRepositoryInterface;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

class ExchangeRatesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ExchangeRates';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'exchange-rates');

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
        $this->app->bind(ExchangeRateRepositoryInterface::class, ExchangeRateRepository::class);
        $this->app->singleton(ExchangeRateRepository::class, fn ($app) => 
            new ExchangeRateRepository(new ExchangeRate())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(UpdateExchangeRateAction::class);
        $this->app->singleton(FreezeExchangeRateAction::class);
        $this->app->singleton(FetchExchangeRatesAction::class);
        $this->app->singleton(CreateRateAlertAction::class);
        $this->app->singleton(DeactivateRateAlertAction::class);
        $this->app->singleton(ConvertCurrencyAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ExchangeRateQueryService::class);
        $this->app->singleton(ExchangeRateCommandService::class);
    }
}
