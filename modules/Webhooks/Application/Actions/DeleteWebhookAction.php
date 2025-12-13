<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

final class DeleteWebhookAction extends Action
{
    public function __construct(
        private readonly WebhookRepository $repository
    ) {}

    public function execute(Webhook $webhook): bool
    {
        return $this->repository->delete($webhook->id);
    }
}
