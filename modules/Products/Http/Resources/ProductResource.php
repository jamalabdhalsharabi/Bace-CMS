<?php

declare(strict_types=1);

namespace Modules\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'type' => $this->type,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'is_featured' => $this->is_featured,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock_status' => $this->stock_status,
            'stock_quantity' => $this->when($this->track_inventory, $this->stock_quantity),
            'requires_shipping' => $this->requires_shipping,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'dimensions' => $this->dimensions,
            'has_variants' => $this->has_variants,
            'variants' => $this->when($this->has_variants, fn() => 
                ProductVariantResource::collection($this->variants)
            ),
            'translations' => $this->when($this->translations->isNotEmpty(), fn() => 
                $this->translations->mapWithKeys(fn($t) => [
                    $t->locale => [
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'short_description' => $t->short_description,
                        'description' => $t->description,
                    ]
                ])
            ),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
