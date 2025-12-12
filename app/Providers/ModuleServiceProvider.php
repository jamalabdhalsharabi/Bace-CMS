<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\FeatureManager;
use App\Services\ModuleLoader;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge modules config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/modules.php',
            'modules'
        );

        // Merge features config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/features.php',
            'features'
        );

        // Register ModuleLoader as singleton
        $this->app->singleton(ModuleLoader::class, function ($app) {
            return (new ModuleLoader())->scan();
        });

        // Register FeatureManager as singleton
        $this->app->singleton(FeatureManager::class, function ($app) {
            return new FeatureManager();
        });

        // Register alias for easy access
        $this->app->alias(ModuleLoader::class, 'modules');
        $this->app->alias(FeatureManager::class, 'features');

        // Register all modules
        $this->app->make(ModuleLoader::class)->register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config files
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/modules.php' => config_path('modules.php'),
                __DIR__ . '/../../config/features.php' => config_path('features.php'),
            ], 'cms-config');

            // Register commands
            $this->commands([
                \App\Console\Commands\ModuleListCommand::class,
                \App\Console\Commands\ModuleMakeCommand::class,
                \App\Console\Commands\ModuleEnableCommand::class,
                \App\Console\Commands\ModuleDisableCommand::class,
                \App\Console\Commands\ModuleMigrateCommand::class,
            ]);
        }

        // Boot all modules
        $this->app->make(ModuleLoader::class)->boot();
    }
}
