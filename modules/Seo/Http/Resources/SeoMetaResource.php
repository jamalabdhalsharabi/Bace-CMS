<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoMetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'locale' => $this->locale,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'canonical_url' => $this->canonical_url,
            'robots' => $this->robots,
            'og' => [
                'title' => $this->og_title,
                'description' => $this->og_description,
                'image' => $this->og_image,
                'type' => $this->og_type,
            ],
            'twitter' => [
                'card' => $this->twitter_card,
                'title' => $this->twitter_title,
                'description' => $this->twitter_description,
                'image' => $this->twitter_image,
            ],
            'schema_markup' => $this->schema_markup,
        ];
    }
}
