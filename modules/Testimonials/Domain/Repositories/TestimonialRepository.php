<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Testimonials\Domain\Contracts\TestimonialRepositoryInterface;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Testimonial Repository Implementation.
 *
 * Concrete implementation of TestimonialRepositoryInterface.
 * Handles all data access operations for testimonials including
 * CRUD, filtering, ordering, and statistics.
 *
 * @extends BaseRepository<Testimonial>
 * @implements TestimonialRepositoryInterface
 *
 * @package Modules\Testimonials\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class TestimonialRepository extends BaseRepository implements TestimonialRepositoryInterface
{
    /**
     * Create a new TestimonialRepository instance.
     *
     * @param Testimonial $model The Testimonial model instance
     */
    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with(['translation', 'avatar']);

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['active'])) {
            $query->where('status', 'published');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getActive(int $limit = 10): Collection
    {
        return $this->query()
            ->with(['translation', 'avatar'])
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getFeatured(int $limit = 6): Collection
    {
        return $this->query()
            ->with(['translation', 'avatar'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getApproved(): Collection
    {
        return $this->query()
            ->with('translations')
            ->where('status', 'approved')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->onlyTrashed()
            ->with(['translation', 'avatar'])
            ->latest('deleted_at')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function restore(string $id): ?Testimonial
    {
        $testimonial = $this->model->newQuery()->withTrashed()->find($id);
        $testimonial?->restore();

        return $testimonial;
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(string $id): bool
    {
        $testimonial = $this->model->newQuery()->withTrashed()->find($id);

        return $testimonial?->forceDelete() ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->query()->where('id', $id)->update(['sort_order' => $index]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingStats(): array
    {
        $query = $this->query()->where('status', 'published');

        return [
            'total' => $query->count(),
            'average' => round((float) $query->avg('rating'), 1),
            'distribution' => $this->query()
                ->where('status', 'published')
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];
    }
}
