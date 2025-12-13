<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

/**
 * Project Query Service.
 */
final class ProjectQueryService
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation', 'featuredImage', 'categories'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Project
    {
        return $this->repository
            ->with(['translations', 'featuredImage', 'categories', 'gallery'])
            ->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Project
    {
        return $this->repository
            ->with(['translations', 'featuredImage', 'categories', 'gallery'])
            ->findBySlug($slug, $locale);
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->repository
            ->with(['translation', 'featuredImage'])
            ->getFeatured($limit);
    }
}
