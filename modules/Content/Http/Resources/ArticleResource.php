<?php

declare(strict_types=1);

namespace Modules\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'allow_comments' => $this->allow_comments,
            'view_count' => $this->view_count,
            'reading_time' => $this->reading_time,
            'published_at' => $this->published_at?->toISOString(),
            'author' => $this->when($this->author, fn () => [
                'id' => $this->author->id,
                'name' => $this->author->full_name,
                'email' => $this->author->email,
            ]),
            'featured_image' => $this->when($this->featuredImage, fn () => [
                'id' => $this->featuredImage->id,
                'url' => $this->featuredImage->url,
                'alt' => $this->featuredImage->alt_text,
            ]),
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'url' => $this->url,
            'translations' => $this->when($this->translations->isNotEmpty(), fn () => 
                $this->translations->mapWithKeys(fn ($t) => [
                    $t->locale => [
                        'title' => $t->title,
                        'slug' => $t->slug,
                        'excerpt' => $t->excerpt,
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
