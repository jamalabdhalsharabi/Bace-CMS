<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaticBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
