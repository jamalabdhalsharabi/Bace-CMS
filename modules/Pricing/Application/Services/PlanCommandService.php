<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Actions\CreatePlanAction;
use Modules\Pricing\Application\Actions\DeletePlanAction;
use Modules\Pricing\Application\Actions\TogglePlanAction;
use Modules\Pricing\Application\Actions\UpdatePlanAction;
use Modules\Pricing\Domain\DTO\PlanData;
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Plan Command Service.
 */
final class PlanCommandService
{
    public function __construct(
        private readonly CreatePlanAction $createAction,
        private readonly UpdatePlanAction $updateAction,
        private readonly DeletePlanAction $deleteAction,
        private readonly TogglePlanAction $toggleAction,
        private readonly PlanRepository $repository,
    ) {}

    public function create(PlanData $data): Plan
    {
        return $this->createAction->execute($data);
    }

    public function update(Plan $plan, PlanData $data): Plan
    {
        return $this->updateAction->execute($plan, $data);
    }

    public function delete(Plan $plan): bool
    {
        return $this->deleteAction->execute($plan);
    }

    public function activate(Plan $plan): Plan
    {
        return $this->toggleAction->activate($plan);
    }

    public function deactivate(Plan $plan): Plan
    {
        return $this->toggleAction->deactivate($plan);
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->repository->update($id, ['sort_order' => $index]);
        }
    }
}
