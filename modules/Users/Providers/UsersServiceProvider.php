<?php

declare(strict_types=1);

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Users\Contracts\UserRepositoryContract;
use Modules\Users\Contracts\UserServiceContract;
use Modules\Users\Repositories\UserRepository;
use Modules\Users\Services\UserService;

class UsersServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Users';
    protected string $moduleNameLower = 'users';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        // Bind contracts
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(UserServiceContract::class, UserService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/admin.php'));

        $this->registerViews();
        $this->registerTranslations();
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-views']);

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $langPath = module_path($this->moduleName, 'Resources/lang');
        $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
    }
}
