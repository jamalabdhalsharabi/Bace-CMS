<?php

declare(strict_types=1);

namespace Modules\Webhooks\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Webhooks\Application\Actions\CreateWebhookAction;
use Modules\Webhooks\Application\Actions\DeleteWebhookAction;
use Modules\Webhooks\Application\Actions\DispatchWebhookAction;
use Modules\Webhooks\Application\Actions\UpdateWebhookAction;
use Modules\Webhooks\Application\Services\WebhookCommandService;
use Modules\Webhooks\Application\Services\WebhookQueryService;
use Modules\Webhooks\Domain\Contracts\WebhookRepositoryInterface;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

/**
 * Webhooks Module Service Provider.
 *
 * @package Modules\Webhooks\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class WebhooksServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Webhooks';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'webhooks');

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
        $this->app->bind(WebhookRepositoryInterface::class, WebhookRepository::class);
        $this->app->singleton(WebhookRepository::class, fn ($app) => new WebhookRepository(new Webhook()));
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateWebhookAction::class);
        $this->app->singleton(UpdateWebhookAction::class);
        $this->app->singleton(DeleteWebhookAction::class);
        $this->app->singleton(DispatchWebhookAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(WebhookQueryService::class);
        $this->app->singleton(WebhookCommandService::class);
    }
}
