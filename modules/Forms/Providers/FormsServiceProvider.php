<?php

declare(strict_types=1);

namespace Modules\Forms\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Forms\Contracts\FormServiceContract;
use Modules\Forms\Services\FormService;

class FormsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Forms';
    protected string $moduleNameLower = 'forms';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(FormServiceContract::class, FormService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
