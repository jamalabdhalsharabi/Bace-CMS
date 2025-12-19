<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Contracts\PlanRepositoryInterface;
use Modules\Pricing\Domain\DTO\PlanData;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Create Plan Action.
 *
 * Single-responsibility action class for creating new pricing plans.
 * Handles the business logic of plan creation including data validation
 * and persistence through the repository layer.
 *
 * This action follows the Action pattern which provides:
 * - Single responsibility for plan creation
 * - Easy unit testing in isolation
 * - Clear separation from controller logic
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see PlanData The DTO containing plan data
 * @see PlanRepository The repository for persistence
 */
final class CreatePlanAction extends Action
{
    /**
     * Create a new CreatePlanAction instance.
     *
     * @param PlanRepository $repository The plan repository for data persistence
     */
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    /**
     * Execute the plan creation action.
     *
     * Creates a new pricing plan with the provided data.
     * The plan is persisted to the database via the repository.
     *
     * @param PlanData $data The validated plan data from request
     *
     * @return PricingPlan The newly created pricing plan model
     *
     * @throws \Illuminate\Database\QueryException If database operation fails
     *
     * @example
     * ```php
     * $data = new PlanData(
     *     name: 'Professional',
     *     slug: 'professional',
     *     price: 99.99,
     *     billing_period: 'monthly',
     * );
     * $plan = $action->execute($data);
     * ```
     */
    public function execute(PlanData $data): PricingPlan
    {
        return $this->repository->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'price' => $data->price,
            'billing_period' => $data->billing_period,
            'features' => $data->features,
            'is_active' => $data->is_active,
            'is_featured' => $data->is_featured,
            'sort_order' => $data->sort_order,
            'trial_days' => $data->trial_days,
            'meta' => $data->meta,
        ]);
    }
}
