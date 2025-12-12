<?php

declare(strict_types=1);

namespace Modules\Search\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Search\Contracts\SearchEngineContract;
use Modules\Search\Contracts\SearchServiceContract;
use Modules\Search\Engines\DatabaseSearchEngine;
use Modules\Search\Engines\MeilisearchEngine;
use Modules\Search\Services\SearchService;

class SearchServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Search';
    protected string $moduleNameLower = 'search';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->app->bind(SearchEngineContract::class, function ($app) {
            $driver = config('search.driver', 'database');

            return match ($driver) {
                'meilisearch' => $app->make(MeilisearchEngine::class),
                default => $app->make(DatabaseSearchEngine::class),
            };
        });

        $this->app->bind(SearchServiceContract::class, SearchService::class);
        $this->app->alias(SearchServiceContract::class, 'search');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Search\Console\Commands\ReindexCommand::class,
            ]);
        }
    }
}
