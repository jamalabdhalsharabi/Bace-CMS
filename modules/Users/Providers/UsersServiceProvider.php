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
use Modules\Users\Domain\Contracts\UserRepositoryInterface;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository as DomainUserRepository;
use Modules\Users\Repositories\UserRepository;

/**
 * Users Module Service Provider.
 *
 * Registers and bootstraps the Users module including:
 * - Repository bindings (Interface to Implementation)
 * - User management actions
 * - Query and Command services
 * - Views, routes, and translations
 *
 * @package Modules\Users\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class UsersServiceProvider extends ServiceProvider
{
    /**
     * Module name for path resolution.
     *
     * @var string
     */
    protected string $moduleName = 'Users';

    /**
     * Lowercase module name for config keys.
     *
     * @var string
     */
    protected string $moduleNameLower = 'users';

    /**
     * Register module services.
     *
     * @return void
     */
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

    /**
     * Register repository bindings.
     *
     * Binds repository interfaces to their concrete implementations.
     *
     * @return void
     */
    protected function registerRepositories(): void
    {
        // Bind interface to implementation for dependency injection
        $this->app->bind(
            UserRepositoryInterface::class,
            DomainUserRepository::class
        );

        // Register concrete repository as singleton
        $this->app->singleton(DomainUserRepository::class, fn ($app) => 
            new DomainUserRepository(new User())
        );

        // Legacy binding for backward compatibility
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
