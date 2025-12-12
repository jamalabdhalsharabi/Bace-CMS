<?php

declare(strict_types=1);

namespace Modules\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Menu\Contracts\MenuServiceContract;
use Modules\Menu\Services\MenuService;

class MenuServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Menu';
    protected string $moduleNameLower = 'menu';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(MenuServiceContract::class, MenuService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
