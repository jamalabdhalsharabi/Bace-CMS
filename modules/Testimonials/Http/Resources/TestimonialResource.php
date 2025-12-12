<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author' => [
                'name' => $this->author_name,
                'title' => $this->author_title,
                'company' => $this->author_company,
                'avatar' => $this->avatar?->url,
            ],
            'content' => $this->content,
            'rating' => $this->rating,
            'is_featured' => $this->is_featured,
            'source' => $this->source,
            'date' => $this->date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
