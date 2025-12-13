<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

final class DeletePlanAction extends Action
{
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    public function execute(Plan $plan): bool
    {
        return $this->repository->delete($plan->id);
    }
}
