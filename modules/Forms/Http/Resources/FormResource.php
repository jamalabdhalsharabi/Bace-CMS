<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'success_message' => $this->success_message,
            'redirect_url' => $this->redirect_url,
            'is_active' => $this->is_active,
            'captcha_enabled' => $this->captcha_enabled,
            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
            'submissions_count' => $this->when(isset($this->submissions_count), $this->submissions_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
