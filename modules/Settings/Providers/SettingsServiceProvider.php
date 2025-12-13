<?php

declare(strict_types=1);

namespace Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Settings\Application\Actions\DeleteSettingAction;
use Modules\Settings\Application\Actions\SetSettingAction;
use Modules\Settings\Application\Services\SettingCommandService;
use Modules\Settings\Application\Services\SettingQueryService;
use Modules\Settings\Domain\Models\Setting;
use Modules\Settings\Domain\Repositories\SettingRepository;

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
        $this->app->singleton(SettingRepository::class, fn ($app) => 
            new SettingRepository(new Setting())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(SetSettingAction::class);
        $this->app->singleton(DeleteSettingAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(SettingQueryService::class);
        $this->app->singleton(SettingCommandService::class);
    }
}
