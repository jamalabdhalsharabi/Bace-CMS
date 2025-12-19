<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Compare Plans Action.
 *
 * Retrieves comparison data for multiple pricing plans.
 * This is a read operation but kept as Action for consistency.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ComparePlansAction extends Action
{
    /**
     * @param PlanRepository $repository Read-only repository for fetching plans
     */
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    /**
     * Execute the compare action.
     *
     * @param array<string> $planSlugs Array of plan slugs to compare
     *
     * @return array<int, array{id: string, name: string, slug: string, price: float, billing_period: string, features: array}>
     */
    public function execute(array $planSlugs): array
    {
        $plans = $this->repository->getBySlugs($planSlugs);
        
        return $plans->map(fn($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => $plan->price,
            'billing_period' => $plan->billing_period,
            'features' => $plan->features,
        ])->toArray();
    }
}
