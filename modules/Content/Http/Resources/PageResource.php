<?php

declare(strict_types=1);

namespace Modules\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'template' => $this->template,
            'status' => $this->status,
            'is_homepage' => $this->is_homepage,
            'ordering' => $this->ordering,
            'published_at' => $this->published_at?->toISOString(),
            'author' => $this->when($this->author, fn () => [
                'id' => $this->author->id,
                'name' => $this->author->full_name,
            ]),
            'featured_image' => $this->when($this->featuredImage, fn () => [
                'id' => $this->featuredImage->id,
                'url' => $this->featuredImage->url,
                'alt' => $this->featuredImage->alt_text,
            ]),
            'title' => $this->title,
            'slug' => $this->slug,
            'full_slug' => $this->full_slug,
            'content' => $this->content,
            'url' => $this->url,
            'children' => $this->when($this->children->isNotEmpty(), fn () => 
                PageResource::collection($this->children)
            ),
            'translations' => $this->when($this->translations->isNotEmpty(), fn () => 
                $this->translations->mapWithKeys(fn ($t) => [
                    $t->locale => [
                        'title' => $t->title,
                        'slug' => $t->slug,
                        'content' => $t->content,
                        'meta_title' => $t->meta_title,
                        'meta_description' => $t->meta_description,
                        'meta_keywords' => $t->meta_keywords,
                    ]
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
