<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan' => [
                'id' => $this->plan->id,
                'slug' => $this->plan->slug,
                'name' => $this->plan->name,
            ],
            'billing_period' => $this->billing_period,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_on_trial' => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'pending_plan' => $this->when($this->pending_plan_id, fn() => [
                'id' => $this->pendingPlan->id,
                'name' => $this->pendingPlan->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
