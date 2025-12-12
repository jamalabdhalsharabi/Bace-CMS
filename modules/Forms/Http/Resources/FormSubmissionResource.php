<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'user' => $this->when($this->user, fn () => [
                'id' => $this->user->id,
                'name' => $this->user->full_name ?? $this->user->email,
            ]),
            'data' => $this->data,
            'ip_address' => $this->ip_address,
            'status' => $this->status,
            'notes' => $this->notes,
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
