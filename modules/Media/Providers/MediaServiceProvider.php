<?php

declare(strict_types=1);

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Contracts\MediaServiceContract;
use Modules\Media\Services\MediaService;

class MediaServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Media';
    protected string $moduleNameLower = 'media';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(MediaServiceContract::class, MediaService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
