<?php

declare(strict_types=1);

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Content\Application\Actions\Article\CreateArticleAction;
use Modules\Content\Application\Actions\Article\DeleteArticleAction;
use Modules\Content\Application\Actions\Article\DuplicateArticleAction;
use Modules\Content\Application\Actions\Article\PublishArticleAction;
use Modules\Content\Application\Actions\Article\UpdateArticleAction;
use Modules\Content\Application\Actions\Page\CreatePageAction;
use Modules\Content\Application\Actions\Page\DeletePageAction;
use Modules\Content\Application\Actions\Page\DuplicatePageAction;
use Modules\Content\Application\Actions\Page\PublishPageAction;
use Modules\Content\Application\Actions\Page\UpdatePageAction;
use Modules\Content\Application\Services\ArticleCommandService;
use Modules\Content\Application\Services\ArticleCommentService;
use Modules\Content\Application\Services\ArticleMediaService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Application\Services\ArticleTaxonomyService;
use Modules\Content\Application\Services\ArticleWorkflowService;
use Modules\Content\Application\Services\PageCommandService;
use Modules\Content\Application\Services\PageQueryService;
use Modules\Content\Domain\Contracts\ArticleRepositoryInterface;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Content\Domain\Repositories\PageRepository;

/**
 * Content Module Service Provider.
 *
 * Registers and bootstraps the Content module including:
 * - Repository bindings (Interface to Implementation)
 * - Action classes for CRUD operations
 * - Query and Command services
 * - Views and migrations
 *
 * @package Modules\Content\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class ContentServiceProvider extends ServiceProvider
{
    /**
     * Module name for path resolution.
     *
     * @var string
     */
    protected string $moduleName = 'Content';

    /**
     * Lowercase module name for config keys.
     *
     * @var string
     */
    protected string $moduleNameLower = 'content';

    /**
     * Register module services.
     *
     * Binds interfaces to implementations and registers
     * all module-specific services in the container.
     *
     * @return void
     */
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

    /**
     * Bootstrap module services.
     *
     * Loads migrations, routes, and views for the module.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
        $this->registerViews();
    }

    /**
     * Register repository bindings.
     *
     * Binds repository interfaces to their concrete implementations.
     * This enables dependency injection and easier testing through mocking.
     *
     * @return void
     */
    protected function registerRepositories(): void
    {
        // Bind interface to implementation for dependency injection
        $this->app->bind(
            ArticleRepositoryInterface::class,
            ArticleRepository::class
        );

        // Register concrete repositories as singletons
        $this->app->singleton(ArticleRepository::class, fn ($app) => 
            new ArticleRepository(new Article())
        );

        $this->app->singleton(PageRepository::class, fn ($app) => 
            new PageRepository(new Page())
        );
    }

    /**
     * Register action classes.
     */
    protected function registerActions(): void
    {
        // Article Actions
        $this->app->singleton(CreateArticleAction::class);
        $this->app->singleton(UpdateArticleAction::class);
        $this->app->singleton(PublishArticleAction::class);
        $this->app->singleton(DeleteArticleAction::class);
        $this->app->singleton(DuplicateArticleAction::class);

        // Page Actions
        $this->app->singleton(CreatePageAction::class);
        $this->app->singleton(UpdatePageAction::class);
        $this->app->singleton(PublishPageAction::class);
        $this->app->singleton(DeletePageAction::class);
        $this->app->singleton(DuplicatePageAction::class);
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        // Article Services
        $this->app->singleton(ArticleQueryService::class);
        $this->app->singleton(ArticleCommandService::class);
        $this->app->singleton(ArticleWorkflowService::class);
        $this->app->singleton(ArticleTaxonomyService::class);
        $this->app->singleton(ArticleMediaService::class);
        $this->app->singleton(ArticleCommentService::class);

        // New architecture services - Pages
        $this->app->singleton(PageQueryService::class);
        $this->app->singleton(PageCommandService::class);
    }

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }
}
