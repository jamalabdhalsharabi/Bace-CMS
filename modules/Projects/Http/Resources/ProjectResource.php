<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'client' => [
                'name' => $this->client_name,
                'logo' => $this->clientLogo?->url,
                'website' => $this->client_website,
            ],
            'project_type' => $this->project_type,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'metrics' => $this->metrics,
            'translations' => $this->when($this->translations->isNotEmpty(), fn() => 
                $this->translations->mapWithKeys(fn($t) => [$t->locale => [
                    'title' => $t->title,
                    'slug' => $t->slug,
                    'excerpt' => $t->excerpt,
                    'description' => $t->description,
                    'challenge' => $t->challenge,
                    'solution' => $t->solution,
                    'results' => $t->results,
                ]])
            ),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
