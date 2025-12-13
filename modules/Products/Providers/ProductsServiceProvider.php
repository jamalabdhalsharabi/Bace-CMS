<?php

declare(strict_types=1);

namespace Modules\Products\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Products\Application\Actions\Product\CreateProductAction;
use Modules\Products\Application\Actions\Product\DeleteProductAction;
use Modules\Products\Application\Actions\Product\DuplicateProductAction;
use Modules\Products\Application\Actions\Product\FeatureProductAction;
use Modules\Products\Application\Actions\Product\PublishProductAction;
use Modules\Products\Application\Actions\Product\UpdateProductAction;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductInventoryService;
use Modules\Products\Application\Services\ProductPricingService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Products Module Service Provider.
 */
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
        $this->app->singleton(ProductRepository::class, fn ($app) => 
            new ProductRepository(new Product())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateProductAction::class);
        $this->app->singleton(UpdateProductAction::class);
        $this->app->singleton(PublishProductAction::class);
        $this->app->singleton(DeleteProductAction::class);
        $this->app->singleton(DuplicateProductAction::class);
        $this->app->singleton(FeatureProductAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(ProductQueryService::class);
        $this->app->singleton(ProductCommandService::class);
        $this->app->singleton(ProductInventoryService::class);
        $this->app->singleton(ProductPricingService::class);
    }
}
