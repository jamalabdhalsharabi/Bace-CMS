<?php

declare(strict_types=1);

namespace Modules\Localization\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Localization\Application\Actions\CreateLanguageAction;
use Modules\Localization\Application\Actions\DeleteLanguageAction;
use Modules\Localization\Application\Actions\SetDefaultLanguageAction;
use Modules\Localization\Application\Actions\UpdateLanguageAction;
use Modules\Localization\Application\Services\LanguageCommandService;
use Modules\Localization\Application\Services\LanguageQueryService;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;
use Modules\Localization\Http\Middleware\SetLocale;

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

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));

        $this->app['router']->aliasMiddleware('locale', SetLocale::class);
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(LanguageRepository::class, fn ($app) => 
            new LanguageRepository(new Language())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateLanguageAction::class);
        $this->app->singleton(UpdateLanguageAction::class);
        $this->app->singleton(DeleteLanguageAction::class);
        $this->app->singleton(SetDefaultLanguageAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton('locale.resolver', fn () => new \Modules\Localization\Services\LocaleResolver());
        $this->app->singleton(LanguageQueryService::class);
        $this->app->singleton(LanguageCommandService::class);
    }
}
