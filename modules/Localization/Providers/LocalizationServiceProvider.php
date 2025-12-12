<?php

declare(strict_types=1);

namespace Modules\Localization\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Localization\Contracts\LanguageServiceContract;
use Modules\Localization\Http\Middleware\SetLocale;
use Modules\Localization\Services\LanguageService;

class LocalizationServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Localization';
    protected string $moduleNameLower = 'localization';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(LanguageServiceContract::class, LanguageService::class);
        $this->app->singleton('locale.resolver', fn () => new \Modules\Localization\Services\LocaleResolver());
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));

        $this->app['router']->aliasMiddleware('locale', SetLocale::class);
    }
}
