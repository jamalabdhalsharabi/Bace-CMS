<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'applies_to_plans' => $this->applies_to_plans,
            'applies_to_periods' => $this->applies_to_periods,
            'usage_limit' => $this->usage_limit,
            'per_user_limit' => $this->per_user_limit,
            'used_count' => $this->used_count,
            'starts_at' => $this->starts_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'first_payment_only' => $this->first_payment_only,
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
