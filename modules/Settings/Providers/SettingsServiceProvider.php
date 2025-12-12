<?php

declare(strict_types=1);

namespace Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Settings\Contracts\SettingsServiceContract;
use Modules\Settings\Services\SettingsService;

class SettingsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Settings';
    protected string $moduleNameLower = 'settings';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->singleton(SettingsServiceContract::class, SettingsService::class);
        $this->app->alias(SettingsServiceContract::class, 'settings');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
