<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

final class UpdateWebhookAction extends Action
{
    public function __construct(
        private readonly WebhookRepository $repository
    ) {}

    public function execute(Webhook $webhook, array $data): Webhook
    {
        $this->repository->update($webhook->id, array_filter([
            'name' => $data['name'] ?? null,
            'url' => $data['url'] ?? null,
            'secret' => $data['secret'] ?? null,
            'events' => $data['events'] ?? null,
            'headers' => $data['headers'] ?? null,
            'is_active' => $data['is_active'] ?? null,
            'retry_count' => $data['retry_count'] ?? null,
            'timeout' => $data['timeout'] ?? null,
        ], fn($v) => $v !== null));

        return $webhook->fresh();
    }
}
