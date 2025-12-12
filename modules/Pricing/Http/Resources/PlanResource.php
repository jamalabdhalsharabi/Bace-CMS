<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'trial_days' => $this->trial_days,
            'status' => $this->status,
            'is_recommended' => $this->is_recommended,
            'is_default' => $this->is_default,
            'billing_periods' => $this->billing_periods,
            'prices' => $this->prices->groupBy('billing_period')->map(fn($prices) =>
                $prices->map(fn($p) => [
                    'currency' => $p->currency?->code,
                    'amount' => $p->amount,
                    'compare_amount' => $p->compare_amount,
                    'discount' => $p->getDiscountPercentage(),
                ])
            ),
            'features' => $this->features->map(fn($f) => [
                'key' => $f->feature_key,
                'label' => $f->label,
                'value' => $f->value,
                'type' => $f->type,
                'is_highlighted' => $f->is_highlighted,
            ]),
            'limits' => $this->limits->pluck('limit_value', 'resource'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
