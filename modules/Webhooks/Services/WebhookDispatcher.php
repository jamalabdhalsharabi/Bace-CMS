<?php

declare(strict_types=1);

namespace Modules\Webhooks\Services;

use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Jobs\DispatchWebhook;

class WebhookDispatcher
{
    public function dispatch(string $event, array $payload): int
    {
        $webhooks = Webhook::active()->forEvent($event)->get();
        $count = 0;

        foreach ($webhooks as $webhook) {
            DispatchWebhook::dispatch($webhook, $event, $payload);
            $count++;
        }

        return $count;
    }

    public function dispatchSync(string $event, array $payload): array
    {
        $webhooks = Webhook::active()->forEvent($event)->get();
        $results = [];

        foreach ($webhooks as $webhook) {
            $results[$webhook->id] = $webhook->trigger($event, $payload);
        }

        return $results;
    }
}
