<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Projects\Domain\Models\Project;

/**
 * Project Repository.
 *
 * @extends BaseRepository<Project>
 */
final class ProjectRepository extends BaseRepository
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', fn ($q) => $q->where('taxonomy_id', $filters['category_id']));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
            );
        }

        return $query->latest()->paginate($perPage);
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Project
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }
}
