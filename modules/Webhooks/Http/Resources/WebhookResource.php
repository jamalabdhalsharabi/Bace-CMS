<?php

declare(strict_types=1);

namespace Modules\Webhooks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->events,
            'is_active' => $this->is_active,
            'failure_count' => $this->failure_count,
            'last_triggered_at' => $this->last_triggered_at?->toISOString(),
            'recent_logs' => $this->when($this->logs->isNotEmpty(), fn() => 
                $this->logs->take(5)->map(fn($l) => [
                    'event' => $l->event,
                    'status' => $l->response_status,
                    'success' => $l->is_successful,
                    'time' => $l->response_time,
                    'created_at' => $l->created_at?->toISOString(),
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
