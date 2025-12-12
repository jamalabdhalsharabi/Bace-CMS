<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Providers;

use Illuminate\Support\ServiceProvider;

class StaticBlocksServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'StaticBlocks';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'static-blocks');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
