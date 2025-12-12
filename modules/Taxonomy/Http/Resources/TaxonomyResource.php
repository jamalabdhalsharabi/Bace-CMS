<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->when($this->type, fn () => [
                'slug' => $this->type->slug,
                'name' => $this->type->name,
            ]),
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'ordering' => $this->ordering,
            'featured_image' => $this->when($this->featuredImage, fn () => [
                'id' => $this->featuredImage->id,
                'url' => $this->featuredImage->url,
            ]),
            'children' => $this->when($this->children->isNotEmpty(), fn () => 
                TaxonomyResource::collection($this->children)
            ),
            'translations' => $this->when($this->translations->isNotEmpty(), fn () => 
                $this->translations->mapWithKeys(fn ($t) => [
                    $t->locale => [
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'description' => $t->description,
                        'meta_title' => $t->meta_title,
                        'meta_description' => $t->meta_description,
                    ]
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
