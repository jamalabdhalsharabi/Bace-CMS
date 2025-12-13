<?php

declare(strict_types=1);

namespace Modules\Comments\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Comments\Application\Actions\ApproveCommentAction;
use Modules\Comments\Application\Actions\CreateCommentAction;
use Modules\Comments\Application\Actions\DeleteCommentAction;
use Modules\Comments\Application\Actions\RejectCommentAction;
use Modules\Comments\Application\Actions\ReportCommentAction;
use Modules\Comments\Application\Actions\SpamCommentAction;
use Modules\Comments\Application\Actions\UpdateCommentAction;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;

class CommentsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Comments';
    protected string $moduleNameLower = 'comments';

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
        $this->app->singleton(CommentRepository::class, fn ($app) => 
            new CommentRepository(new Comment())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateCommentAction::class);
        $this->app->singleton(UpdateCommentAction::class);
        $this->app->singleton(DeleteCommentAction::class);
        $this->app->singleton(ApproveCommentAction::class);
        $this->app->singleton(RejectCommentAction::class);
        $this->app->singleton(SpamCommentAction::class);
        $this->app->singleton(ReportCommentAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(CommentQueryService::class);
        $this->app->singleton(CommentCommandService::class);
    }
}
