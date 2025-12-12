<?php

declare(strict_types=1);

namespace Modules\Webhooks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'to' => $this->to,
            'subject' => $this->subject,
            'status' => $this->status,
            'opened_at' => $this->opened_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
