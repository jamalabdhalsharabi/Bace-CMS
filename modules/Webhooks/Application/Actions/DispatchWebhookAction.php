<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Illuminate\Support\Facades\Http;
use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Domain\Models\WebhookLog;
use Modules\Webhooks\Domain\Repositories\WebhookRepository;

final class DispatchWebhookAction extends Action
{
    public function __construct(
        private readonly WebhookRepository $repository
    ) {}

    public function execute(string $event, array $payload): int
    {
        $webhooks = $this->repository->getActiveByEvent($event);
        $dispatched = 0;

        foreach ($webhooks as $webhook) {
            $this->dispatch($webhook, $event, $payload);
            $dispatched++;
        }

        return $dispatched;
    }

    private function dispatch(Webhook $webhook, string $event, array $payload): void
    {
        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

        $headers = array_merge($webhook->headers ?? [], [
            'X-Webhook-Event' => $event,
            'X-Webhook-Signature' => $signature,
        ]);

        $startTime = microtime(true);

        try {
            $response = Http::timeout($webhook->timeout)
                ->withHeaders($headers)
                ->post($webhook->url, $payload);

            $this->logWebhook($webhook, $event, $payload, [
                'status_code' => $response->status(),
                'response' => $response->body(),
                'duration_ms' => (microtime(true) - $startTime) * 1000,
                'success' => $response->successful(),
            ]);
        } catch (\Exception $e) {
            $this->logWebhook($webhook, $event, $payload, [
                'status_code' => 0,
                'response' => $e->getMessage(),
                'duration_ms' => (microtime(true) - $startTime) * 1000,
                'success' => false,
            ]);
        }
    }

    private function logWebhook(Webhook $webhook, string $event, array $payload, array $result): void
    {
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'status_code' => $result['status_code'],
            'response' => $result['response'],
            'duration_ms' => $result['duration_ms'],
            'success' => $result['success'],
        ]);
    }
}
