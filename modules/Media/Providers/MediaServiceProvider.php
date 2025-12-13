<?php

declare(strict_types=1);

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Application\Actions\CreateFolderAction;
use Modules\Media\Application\Actions\DeleteFolderAction;
use Modules\Media\Application\Actions\DeleteMediaAction;
use Modules\Media\Application\Actions\DuplicateMediaAction;
use Modules\Media\Application\Actions\MoveMediaAction;
use Modules\Media\Application\Actions\UpdateMediaAction;
use Modules\Media\Application\Actions\UploadMediaAction;
use Modules\Media\Application\Services\MediaCommandService;
use Modules\Media\Application\Services\MediaQueryService;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

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
        $this->app->singleton(MediaRepository::class, fn ($app) => 
            new MediaRepository(new Media())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(UploadMediaAction::class);
        $this->app->singleton(UpdateMediaAction::class);
        $this->app->singleton(DeleteMediaAction::class);
        $this->app->singleton(MoveMediaAction::class);
        $this->app->singleton(DuplicateMediaAction::class);
        $this->app->singleton(CreateFolderAction::class);
        $this->app->singleton(DeleteFolderAction::class);
    }

    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(MediaQueryService::class);
        $this->app->singleton(MediaCommandService::class);
    }
}
