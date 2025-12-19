<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Actions\ClonePlanAction;
use Modules\Pricing\Application\Actions\ComparePlansAction;
use Modules\Pricing\Application\Actions\CreatePlanAction;
use Modules\Pricing\Application\Actions\DeletePlanAction;
use Modules\Pricing\Application\Actions\ImportPlansAction;
use Modules\Pricing\Application\Actions\LinkPlanAction;
use Modules\Pricing\Application\Actions\ReorderPlansAction;
use Modules\Pricing\Application\Actions\SetDefaultPlanAction;
use Modules\Pricing\Application\Actions\SetRecommendedPlanAction;
use Modules\Pricing\Application\Actions\TogglePlanAction;
use Modules\Pricing\Application\Actions\UnlinkPlanAction;
use Modules\Pricing\Application\Actions\UpdatePlanAction;
use Modules\Pricing\Domain\DTO\PlanData;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Plan Command Service.
 *
 * Orchestrates all write operations via Action classes.
 * No direct Repository or Model usage - delegates to Actions only.
 */
final class PlanCommandService
{
    public function __construct(
        private readonly CreatePlanAction $createAction,
        private readonly UpdatePlanAction $updateAction,
        private readonly DeletePlanAction $deleteAction,
        private readonly TogglePlanAction $toggleAction,
        private readonly SetDefaultPlanAction $setDefaultAction,
        private readonly SetRecommendedPlanAction $setRecommendedAction,
        private readonly ClonePlanAction $cloneAction,
        private readonly ComparePlansAction $compareAction,
        private readonly ImportPlansAction $importAction,
        private readonly LinkPlanAction $linkAction,
        private readonly UnlinkPlanAction $unlinkAction,
        private readonly ReorderPlansAction $reorderAction,
    ) {}

    public function create(PlanData $data): PricingPlan
    {
        return $this->createAction->execute($data);
    }

    public function update(PricingPlan $plan, PlanData $data): PricingPlan
    {
        return $this->updateAction->execute($plan, $data);
    }

    public function delete(PricingPlan $plan): bool
    {
        return $this->deleteAction->execute($plan);
    }

    public function activate(PricingPlan $plan): PricingPlan
    {
        return $this->toggleAction->activate($plan);
    }

    public function deactivate(PricingPlan $plan): PricingPlan
    {
        return $this->toggleAction->deactivate($plan);
    }

    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }

    public function setAsDefault(PricingPlan $plan): PricingPlan
    {
        return $this->setDefaultAction->execute($plan);
    }

    public function setAsRecommended(PricingPlan $plan): PricingPlan
    {
        return $this->setRecommendedAction->execute($plan);
    }

    public function compare(array $planSlugs): array
    {
        return $this->compareAction->execute($planSlugs);
    }

    public function clone(string $id, string $newSlug): PricingPlan
    {
        return $this->cloneAction->execute($id, $newSlug);
    }

    public function import(array $data, string $mode = 'merge'): array
    {
        return $this->importAction->execute($data, $mode);
    }

    public function link(string $planId, string $entityType, string $entityId, bool $isRequired = false): object
    {
        return $this->linkAction->execute($planId, $entityType, $entityId, $isRequired);
    }

    public function unlink(string $planId, string $entityType, string $entityId): bool
    {
        return $this->unlinkAction->execute($planId, $entityType, $entityId);
    }
}
