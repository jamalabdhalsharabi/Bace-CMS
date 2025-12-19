<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Set Recommended Plan Action.
 *
 * Sets a pricing plan as the recommended option.
 * Ensures only one plan can be recommended at a time.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SetRecommendedPlanAction extends Action
{
    /**
     * Execute the set recommended action.
     *
     * @param PricingPlan $plan The plan to set as recommended
     *
     * @return PricingPlan The updated plan
     */
    public function execute(PricingPlan $plan): PricingPlan
    {
        // Remove recommended from all other plans
        PricingPlan::where('is_recommended', true)->update(['is_recommended' => false]);
        
        // Set this plan as recommended
        $plan->update(['is_recommended' => true]);
        
        return $plan->fresh();
    }
}
