<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'symbol_position' => $this->symbol_position,
            'decimal_separator' => $this->decimal_separator,
            'thousand_separator' => $this->thousand_separator,
            'decimal_places' => $this->decimal_places,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'ordering' => $this->ordering,
        ];
    }
}
