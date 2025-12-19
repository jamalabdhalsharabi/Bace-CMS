<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Plan Repository Interface.
 *
 * Defines the contract for pricing plan data access operations.
 * Extends the base RepositoryInterface with plan-specific methods
 * for managing subscription plans and pricing tiers.
 *
 * This interface provides methods for:
 * - Retrieving active and featured plans
 * - Finding plans by slug
 * - Ordering plans by sort order
 *
 * @extends RepositoryInterface<PricingPlan>
 *
 * @package Modules\Pricing\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see \Modules\Pricing\Domain\Repositories\PlanRepository Default implementation
 */
interface PlanRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all active pricing plans.
     *
     * Retrieves all plans that are currently active and available
     * for subscription. Results are ordered by sort_order.
     *
     * @return Collection<int, PricingPlan> Collection of active plans
     *
     * @example
     * ```php
     * $activePlans = $repository->getActive();
     * // Display in pricing page
     * ```
     */
    public function getActive(): Collection;

    /**
     * Get featured pricing plans.
     *
     * Retrieves active plans that are marked as featured.
     * Useful for highlighting recommended or popular plans.
     *
     * @return Collection<int, PricingPlan> Collection of featured plans
     *
     * @example
     * ```php
     * $featuredPlans = $repository->getFeatured();
     * // Highlight on homepage
     * ```
     */
    public function getFeatured(): Collection;

    /**
     * Find a plan by its URL slug.
     *
     * Searches for a pricing plan by its unique slug identifier.
     *
     * @param string $slug The URL-friendly slug
     *
     * @return PricingPlan|null The plan if found, null otherwise
     *
     * @example
     * ```php
     * $plan = $repository->findBySlug('professional');
     * if ($plan) {
     *     // Display plan details
     * }
     * ```
     */
    public function findBySlug(string $slug): ?PricingPlan;
}
