<?php

declare(strict_types=1);

namespace Modules\Services\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Services\Domain\Models\Service;

/**
 * Service Repository.
 *
 * Read-only repository for Service model queries.
 * All write operations must be performed through Action classes.
 *
 * @extends BaseRepository<Service>
 *
 * @package Modules\Services\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ServiceRepository extends BaseRepository
{
    /**
     * Create a new ServiceRepository instance.
     *
     * @param Service $model The Service model instance
     */
    public function __construct(Service $model)
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

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
            );
        }

        return $query->ordered()->paginate($perPage);
    }

    public function getPublished(): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->ordered()
            ->get();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Service
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }
}
