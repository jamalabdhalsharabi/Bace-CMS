<?php

declare(strict_types=1);

namespace Modules\Seo\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Seo\Application\Actions\CreateRedirectAction;
use Modules\Seo\Application\Actions\DeleteRedirectAction;
use Modules\Seo\Application\Actions\LogPageViewAction;
use Modules\Seo\Application\Actions\UpdateSeoMetaAction;
use Modules\Seo\Application\Services\SeoCommandService;
use Modules\Seo\Application\Services\SeoQueryService;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Models\SeoMeta;
use Modules\Seo\Domain\Repositories\RedirectRepository;
use Modules\Seo\Domain\Repositories\SeoMetaRepository;

class SeoServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Seo';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'seo');

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
        $this->app->singleton(SeoMetaRepository::class, fn ($app) => new SeoMetaRepository(new SeoMeta()));
        $this->app->singleton(RedirectRepository::class, fn ($app) => new RedirectRepository(new Redirect()));
    }

    protected function registerActions(): void
    {
        $this->app->singleton(UpdateSeoMetaAction::class);
        $this->app->singleton(CreateRedirectAction::class);
        $this->app->singleton(DeleteRedirectAction::class);
        $this->app->singleton(LogPageViewAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(SeoQueryService::class);
        $this->app->singleton(SeoCommandService::class);
    }
}
