<?php

declare(strict_types=1);

namespace Modules\Comments\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Comments\Contracts\CommentServiceContract;
use Modules\Comments\Services\CommentService;

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

        $this->app->bind(CommentServiceContract::class, CommentService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
