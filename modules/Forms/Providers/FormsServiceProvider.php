<?php

declare(strict_types=1);

namespace Modules\Forms\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Forms\Application\Actions\CreateFormAction;
use Modules\Forms\Application\Actions\DeleteFormAction;
use Modules\Forms\Application\Actions\DuplicateFormAction;
use Modules\Forms\Application\Actions\SubmitFormAction;
use Modules\Forms\Application\Actions\ToggleFormAction;
use Modules\Forms\Application\Actions\UpdateFormAction;
use Modules\Forms\Application\Services\FormCommandService;
use Modules\Forms\Application\Services\FormQueryService;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Repositories\FormRepository;

class FormsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Forms';
    protected string $moduleNameLower = 'forms';

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
        $this->app->singleton(FormRepository::class, fn ($app) => 
            new FormRepository(new Form())
        );
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateFormAction::class);
        $this->app->singleton(UpdateFormAction::class);
        $this->app->singleton(DeleteFormAction::class);
        $this->app->singleton(DuplicateFormAction::class);
        $this->app->singleton(ToggleFormAction::class);
        $this->app->singleton(SubmitFormAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(FormQueryService::class);
        $this->app->singleton(FormCommandService::class);
    }
}
