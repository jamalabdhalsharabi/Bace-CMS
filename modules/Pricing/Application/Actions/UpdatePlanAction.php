<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\DTO\PlanData;
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

final class UpdatePlanAction extends Action
{
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    public function execute(Plan $plan, PlanData $data): Plan
    {
        $this->repository->update($plan->id, [
            'name' => $data->name,
            'description' => $data->description ?? $plan->description,
            'price' => $data->price,
            'billing_period' => $data->billing_period,
            'features' => $data->features,
            'is_active' => $data->is_active,
            'is_featured' => $data->is_featured,
            'sort_order' => $data->sort_order,
            'trial_days' => $data->trial_days ?? $plan->trial_days,
            'meta' => $data->meta ?? $plan->meta,
        ]);

        return $plan->fresh();
    }
}
