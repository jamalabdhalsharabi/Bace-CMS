<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Taxonomy\Application\Actions\CreateTaxonomyAction;
use Modules\Taxonomy\Application\Actions\DeleteTaxonomyAction;
use Modules\Taxonomy\Application\Actions\MoveTaxonomyAction;
use Modules\Taxonomy\Application\Actions\ReorderTaxonomyAction;
use Modules\Taxonomy\Application\Actions\UpdateTaxonomyAction;
use Modules\Taxonomy\Application\Services\TaxonomyCommandService;
use Modules\Taxonomy\Application\Services\TaxonomyQueryService;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

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
        $this->app->singleton(TaxonomyRepository::class, fn ($app) => 
            new TaxonomyRepository(new Taxonomy())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateTaxonomyAction::class);
        $this->app->singleton(UpdateTaxonomyAction::class);
        $this->app->singleton(DeleteTaxonomyAction::class);
        $this->app->singleton(ReorderTaxonomyAction::class);
        $this->app->singleton(MoveTaxonomyAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(TaxonomyQueryService::class);
        $this->app->singleton(TaxonomyCommandService::class);
    }
}
