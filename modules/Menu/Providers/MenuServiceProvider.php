<?php

declare(strict_types=1);

namespace Modules\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Menu\Application\Actions\CreateMenuAction;
use Modules\Menu\Application\Actions\DeleteMenuAction;
use Modules\Menu\Application\Actions\ManageMenuItemAction;
use Modules\Menu\Application\Actions\ToggleMenuAction;
use Modules\Menu\Application\Actions\UpdateMenuAction;
use Modules\Menu\Application\Services\MenuCommandService;
use Modules\Menu\Application\Services\MenuQueryService;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

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
        $this->app->singleton(MenuRepository::class, fn ($app) => 
            new MenuRepository(new Menu())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateMenuAction::class);
        $this->app->singleton(UpdateMenuAction::class);
        $this->app->singleton(DeleteMenuAction::class);
        $this->app->singleton(ToggleMenuAction::class);
        $this->app->singleton(ManageMenuItemAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(MenuQueryService::class);
        $this->app->singleton(MenuCommandService::class);
    }
}
