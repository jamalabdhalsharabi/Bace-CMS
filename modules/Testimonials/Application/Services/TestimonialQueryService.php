<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

/**
 * Testimonial Query Service - handles all read operations.
 */
final class TestimonialQueryService
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getActive(int $limit = 10): Collection
    {
        return $this->repository->getActive($limit);
    }

    public function getApproved(): Collection
    {
        return $this->repository->getApproved();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->repository->getFeatured($limit);
    }

    public function find(string $id): ?Testimonial
    {
        return $this->repository->find($id);
    }

    public function findWithTrashed(string $id): ?Testimonial
    {
        return $this->repository->query()->withTrashed()->find($id);
    }

    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getTrashed($perPage);
    }

    public function getRatingStats(): array
    {
        return $this->repository->getRatingStats();
    }
}
