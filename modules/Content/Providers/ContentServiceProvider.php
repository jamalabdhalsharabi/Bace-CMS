<?php

declare(strict_types=1);

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Content\Contracts\ArticleServiceContract;
use Modules\Content\Contracts\PageServiceContract;
use Modules\Content\Services\ArticleService;
use Modules\Content\Services\PageService;

class ContentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Content';
    protected string $moduleNameLower = 'content';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(ArticleServiceContract::class, ArticleService::class);
        $this->app->bind(PageServiceContract::class, PageService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));

        $this->registerViews();
    }

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }
}
