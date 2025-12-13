<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

final class TogglePlanAction extends Action
{
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    public function activate(Plan $plan): Plan
    {
        $this->repository->update($plan->id, ['is_active' => true]);

        return $plan->fresh();
    }

    public function deactivate(Plan $plan): Plan
    {
        $this->repository->update($plan->id, ['is_active' => false]);

        return $plan->fresh();
    }
}
