<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Set Default Plan Action.
 *
 * Sets a pricing plan as the default option.
 * Ensures only one plan can be default at a time.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SetDefaultPlanAction extends Action
{
    /**
     * Execute the set default action.
     *
     * @param PricingPlan $plan The plan to set as default
     *
     * @return PricingPlan The updated plan
     */
    public function execute(PricingPlan $plan): PricingPlan
    {
        // Remove default from all other plans
        PricingPlan::where('is_default', true)->update(['is_default' => false]);
        
        // Set this plan as default
        $plan->update(['is_default' => true]);
        
        return $plan->fresh();
    }
}
