<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Plan Query Service.
 *
 * Handles all read operations for pricing plans via Repository pattern.
 * No direct Model usage - delegates to Repository only.
 *
 * @package Modules\Pricing\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PlanQueryService
{
    /**
     * Create a new PlanQueryService instance.
     *
     * @param PlanRepository $repository The plan repository
     */
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?PricingPlan
    {
        return $this->repository->find($id);
    }

    public function findBySlug(string $slug): ?PricingPlan
    {
        return $this->repository->findBySlug($slug);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getFeatured(): Collection
    {
        return $this->repository->getFeatured();
    }

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function getAnalytics(PricingPlan $plan): array
    {
        return $this->repository->getAnalytics($plan->id);
    }

    public function export(array $filters = []): array
    {
        return $this->repository->exportAll($filters);
    }

    public function getLinks(PricingPlan $plan): array
    {
        return $this->repository->getLinks($plan->id);
    }
}
