<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedirectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_path' => $this->source_path,
            'target_path' => $this->target_path,
            'status_code' => $this->status_code,
            'is_active' => $this->is_active,
            'is_regex' => $this->is_regex,
            'hits_count' => $this->hits_count,
            'last_hit_at' => $this->last_hit_at?->toISOString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
