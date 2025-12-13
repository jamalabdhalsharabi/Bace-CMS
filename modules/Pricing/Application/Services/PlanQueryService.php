<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Pricing\Domain\Models\Plan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Plan Query Service.
 */
final class PlanQueryService
{
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?Plan
    {
        return $this->repository->find($id);
    }

    public function findBySlug(string $slug): ?Plan
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
}
