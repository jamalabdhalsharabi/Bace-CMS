<?php

declare(strict_types=1);

namespace Modules\Products\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\Services\ProductService;

class ProductsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Products';
    protected string $moduleNameLower = 'products';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(ProductServiceContract::class, ProductService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
