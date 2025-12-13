<?php

declare(strict_types=1);

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Users\Application\Actions\ActivateUserAction;
use Modules\Users\Application\Actions\ChangePasswordAction;
use Modules\Users\Application\Actions\CreateUserAction;
use Modules\Users\Application\Actions\DeleteUserAction;
use Modules\Users\Application\Actions\RemoveAvatarAction;
use Modules\Users\Application\Actions\SuspendUserAction;
use Modules\Users\Application\Actions\UpdateAvatarAction;
use Modules\Users\Application\Actions\UpdateProfileAction;
use Modules\Users\Application\Actions\UpdateUserAction;
use Modules\Users\Application\Services\UserCommandService;
use Modules\Users\Application\Services\UserQueryService;
use Modules\Users\Contracts\UserRepositoryContract;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository as DomainUserRepository;
use Modules\Users\Repositories\UserRepository;

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

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(DomainUserRepository::class, fn ($app) => 
            new DomainUserRepository(new User())
        );

        // Legacy binding
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateUserAction::class);
        $this->app->singleton(UpdateUserAction::class);
        $this->app->singleton(DeleteUserAction::class);
        $this->app->singleton(ActivateUserAction::class);
        $this->app->singleton(SuspendUserAction::class);
        $this->app->singleton(ChangePasswordAction::class);
        $this->app->singleton(UpdateAvatarAction::class);
        $this->app->singleton(RemoveAvatarAction::class);
        $this->app->singleton(UpdateProfileAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(UserQueryService::class);
        $this->app->singleton(UserCommandService::class);
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
