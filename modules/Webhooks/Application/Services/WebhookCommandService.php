<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Services;

use Modules\Webhooks\Application\Actions\CreateWebhookAction;
use Modules\Webhooks\Application\Actions\DeleteWebhookAction;
use Modules\Webhooks\Application\Actions\DispatchWebhookAction;
use Modules\Webhooks\Application\Actions\UpdateWebhookAction;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Models\WebhookLog;

final class WebhookCommandService
{
    public function __construct(
        private readonly CreateWebhookAction $createAction,
        private readonly UpdateWebhookAction $updateAction,
        private readonly DeleteWebhookAction $deleteAction,
        private readonly DispatchWebhookAction $dispatchAction,
    ) {}

    public function create(array $data): Webhook
    {
        return $this->createAction->execute($data);
    }

    public function update(Webhook $webhook, array $data): Webhook
    {
        return $this->updateAction->execute($webhook, $data);
    }

    public function delete(Webhook $webhook): bool
    {
        return $this->deleteAction->execute($webhook);
    }

    public function dispatch(string $event, array $payload): int
    {
        return $this->dispatchAction->execute($event, $payload);
    }

    public function cleanOldLogs(int $days = 30): int
    {
        return WebhookLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}
