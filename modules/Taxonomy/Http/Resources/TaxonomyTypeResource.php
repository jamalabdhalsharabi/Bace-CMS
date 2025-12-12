<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'names' => $this->getLocalizedNames(),
            'is_hierarchical' => $this->is_hierarchical,
            'is_multiple' => $this->is_multiple,
            'applies_to' => $this->applies_to,
        ];
    }
}
