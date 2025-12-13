<?php

declare(strict_types=1);

namespace Modules\Pricing\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Interface PlanServiceContract
 * 
 * Defines the contract for pricing plan management services.
 * Handles CRUD, activation, defaults, comparisons, cloning,
 * analytics, import/export, and entity linking.
 * 
 * @package Modules\Pricing\Contracts
 */
interface PlanServiceContract
{
    /**
     * Retrieves a list of pricing plans based on the given filters.
     * 
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    /**
     * Retrieves a collection of active pricing plans.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection;
    /**
     * Finds a pricing plan by its ID.
     * 
     * @param string $id
     * @return ?PricingPlan
     */
    public function find(string $id): ?PricingPlan;
    /**
     * Finds a pricing plan by its slug.
     * 
     * @param string $slug
     * @return ?PricingPlan
     */
    public function findBySlug(string $slug): ?PricingPlan;
    /**
     * Creates a new pricing plan.
     * 
     * @param array $data
     * @return PricingPlan
     */
    public function create(array $data): PricingPlan;
    /**
     * Updates an existing pricing plan.
     * 
     * @param PricingPlan $plan
     * @param array $data
     * @return PricingPlan
     */
    public function update(PricingPlan $plan, array $data): PricingPlan;
    public function delete(PricingPlan $plan): bool;
    public function activate(PricingPlan $plan): PricingPlan;
    public function deactivate(PricingPlan $plan): PricingPlan;
    public function setAsDefault(PricingPlan $plan): PricingPlan;
    public function setAsRecommended(PricingPlan $plan): PricingPlan;
    public function compare(array $planIds): array;
    public function clone(PricingPlan $plan, string $newSlug): PricingPlan;
    public function reorder(array $order): bool;
    public function getAnalytics(PricingPlan $plan): array;
    public function export(array $options = []): array;
    public function import(array $data, string $mode = 'merge'): array;
    public function link(PricingPlan $plan, string $entityType, string $entityId, bool $isRequired = false): \Modules\Pricing\Domain\Models\PlanLink;
    public function unlink(PricingPlan $plan, string $entityType, string $entityId): bool;
    public function getLinks(PricingPlan $plan): \Illuminate\Database\Eloquent\Collection;
}