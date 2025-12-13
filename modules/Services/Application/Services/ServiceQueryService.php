<?php

declare(strict_types=1);

namespace Modules\Services\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Services\Domain\Models\Service;
use Modules\Services\Domain\Repositories\ServiceRepository;

/**
 * Service Query Service.
 */
final class ServiceQueryService
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation', 'featuredImage'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Service
    {
        return $this->repository
            ->with(['translations', 'featuredImage', 'categories', 'media'])
            ->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Service
    {
        return $this->repository
            ->with(['translations', 'featuredImage'])
            ->findBySlug($slug, $locale);
    }

    public function getPublished(): Collection
    {
        return $this->repository
            ->with(['translation', 'featuredImage'])
            ->getPublished();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->repository
            ->with(['translation', 'featuredImage'])
            ->getFeatured($limit);
    }
}
