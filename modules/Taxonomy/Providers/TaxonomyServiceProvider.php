<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Taxonomy\Contracts\TaxonomyServiceContract;
use Modules\Taxonomy\Services\TaxonomyService;

class TaxonomyServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Taxonomy';
    protected string $moduleNameLower = 'taxonomy';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(TaxonomyServiceContract::class, TaxonomyService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
