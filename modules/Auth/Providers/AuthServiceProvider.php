<?php

declare(strict_types=1);

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Application\Actions\LoginAction;
use Modules\Auth\Application\Actions\LogoutAction;
use Modules\Auth\Application\Actions\RegisterAction;
use Modules\Auth\Application\Actions\ResetPasswordAction;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Application\Services\AuthQueryService;
use Modules\Auth\Contracts\AuthServiceContract;
use Modules\Auth\Contracts\PermissionServiceContract;
use Modules\Auth\Contracts\RoleServiceContract;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\PermissionService;
use Modules\Auth\Services\RoleService;

class AuthServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Auth';
    protected string $moduleNameLower = 'auth';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->registerActions();
        $this->registerServices();
    }

    protected function registerActions(): void
    {
        $this->app->singleton(LoginAction::class);
        $this->app->singleton(RegisterAction::class);
        $this->app->singleton(LogoutAction::class);
        $this->app->singleton(ResetPasswordAction::class);
    }

    protected function registerServices(): void
    {
        // Legacy bindings
        $this->app->bind(AuthServiceContract::class, AuthService::class);
        $this->app->bind(RoleServiceContract::class, RoleService::class);
        $this->app->bind(PermissionServiceContract::class, PermissionService::class);

        // New architecture
        $this->app->singleton(AuthQueryService::class);
        $this->app->singleton(AuthCommandService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));

        $this->registerGates();
    }

    protected function registerGates(): void
    {
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermission')) {
                if ($user->hasPermission($ability)) {
                    return true;
                }
            }

            return null;
        });
    }
}
