<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Clone Plan Action.
 *
 * Creates a duplicate of an existing pricing plan with a new slug.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ClonePlanAction extends Action
{
    /**
     * @param PlanRepository $repository Read-only repository for fetching original plan
     */
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    /**
     * Execute the clone action.
     *
     * @param string $id The ID of the plan to clone
     * @param string $newSlug The slug for the cloned plan
     *
     * @return PricingPlan The cloned plan
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If original plan not found
     */
    public function execute(string $id, string $newSlug): PricingPlan
    {
        $original = $this->repository->find($id);
        
        if (!$original) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Plan not found: {$id}");
        }

        $clone = $original->replicate();
        $clone->slug = $newSlug;
        $clone->name = $original->name . ' (Copy)';
        $clone->is_default = false;
        $clone->is_recommended = false;
        $clone->save();

        return $clone;
    }
}
