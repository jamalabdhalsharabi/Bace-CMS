<?php

declare(strict_types=1);

namespace Modules\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'options' => $this->options,
            'price' => $this->price,
            'stock_status' => $this->stock_status,
            'weight' => $this->weight,
        ];
    }
}
