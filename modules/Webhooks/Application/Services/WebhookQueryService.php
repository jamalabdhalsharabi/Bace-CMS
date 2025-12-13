<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Models\WebhookLog;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

final class WebhookQueryService
{
    public function __construct(
        private readonly WebhookRepository $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function findById(string $id): ?Webhook
    {
        return $this->repository->find($id);
    }

    public function getLogs(string $webhookId, ?int $limit = 50): Collection
    {
        return WebhookLog::where('webhook_id', $webhookId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentLogs(?int $limit = 100): Collection
    {
        return WebhookLog::with('webhook')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
