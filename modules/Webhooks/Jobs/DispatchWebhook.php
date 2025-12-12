<?php

declare(strict_types=1);

namespace Modules\Webhooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Webhooks\Domain\Models\Webhook;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {
        $this->tries = config('webhooks.retry_times', 3);
        $this->backoff = config('webhooks.retry_delay', 60);
    }

    public function handle(): void
    {
        $log = $this->webhook->trigger($this->event, $this->payload);

        if (!$log->is_successful && $this->attempts() < $this->tries) {
            $this->release($this->backoff);
        }
    }
}
