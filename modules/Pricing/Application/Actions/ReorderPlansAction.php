<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Reorder Plans Action.
 *
 * Updates the sort order of pricing plans.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderPlansAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of plan IDs in desired order (index = sort_order)
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            PricingPlan::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
