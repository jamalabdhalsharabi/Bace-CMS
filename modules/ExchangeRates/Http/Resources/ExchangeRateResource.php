<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'base_currency' => [
                'id' => $this->base_currency_id,
                'code' => $this->baseCurrency?->code,
                'name' => $this->baseCurrency?->name,
            ],
            'target_currency' => [
                'id' => $this->target_currency_id,
                'code' => $this->targetCurrency?->code,
                'name' => $this->targetCurrency?->name,
            ],
            'rate' => (float) $this->rate,
            'inverse_rate' => (float) $this->inverse_rate,
            'provider' => $this->provider,
            'is_frozen' => $this->is_frozen,
            'frozen_at' => $this->frozen_at?->toISOString(),
            'valid_from' => $this->valid_from?->toISOString(),
            'valid_until' => $this->valid_until?->toISOString(),
            'is_active' => $this->isActive(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
