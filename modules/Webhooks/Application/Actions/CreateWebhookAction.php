<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

final class CreateWebhookAction extends Action
{
    public function __construct(
        private readonly WebhookRepository $repository
    ) {}

    public function execute(array $data): Webhook
    {
        return $this->repository->create([
            'name' => $data['name'],
            'url' => $data['url'],
            'secret' => $data['secret'] ?? Str::random(32),
            'events' => $data['events'] ?? [],
            'headers' => $data['headers'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'retry_count' => $data['retry_count'] ?? 3,
            'timeout' => $data['timeout'] ?? 30,
            'created_by' => $this->userId(),
        ]);
    }
}
