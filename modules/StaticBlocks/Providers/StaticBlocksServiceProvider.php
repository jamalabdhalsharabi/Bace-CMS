<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\StaticBlocks\Application\Actions\CreateStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\DeleteStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\UpdateStaticBlockAction;
use Modules\StaticBlocks\Application\Services\StaticBlockCommandService;
use Modules\StaticBlocks\Application\Services\StaticBlockQueryService;
use Modules\StaticBlocks\Domain\Contracts\StaticBlockRepositoryInterface;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

/**
 * Static Blocks Module Service Provider.
 *
 * @package Modules\StaticBlocks\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class StaticBlocksServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'StaticBlocks';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'static-blocks');

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
        $this->app->bind(StaticBlockRepositoryInterface::class, StaticBlockRepository::class);
        $this->app->singleton(StaticBlockRepository::class, fn ($app) => new StaticBlockRepository(new StaticBlock()));
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateStaticBlockAction::class);
        $this->app->singleton(UpdateStaticBlockAction::class);
        $this->app->singleton(DeleteStaticBlockAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(StaticBlockQueryService::class);
        $this->app->singleton(StaticBlockCommandService::class);
    }
}
