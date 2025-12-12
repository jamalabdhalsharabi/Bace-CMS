<?php

declare(strict_types=1);

namespace Modules\Services\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->translation?->short_description,
            'features' => $this->translation?->features,
            'benefits' => $this->translation?->benefits,
            'process' => $this->translation?->process,
            'faq' => $this->translation?->faq,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'published_at' => $this->published_at?->toISOString(),
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'archived_at' => $this->archived_at?->toISOString(),
            'translations' => $this->whenLoaded('translations', fn() => 
                $this->translations->keyBy('locale')->map(fn($t) => [
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'short_description' => $t->short_description,
                    'description' => $t->description,
                ])
            ),
            'categories' => $this->whenLoaded('categories', fn() => 
                $this->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])
            ),
            'related_services' => $this->whenLoaded('relatedServices', fn() => 
                $this->relatedServices->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug])
            ),
            'meta' => [
                'title' => $this->translation?->meta_title,
                'description' => $this->translation?->meta_description,
                'keywords' => $this->translation?->meta_keywords,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
