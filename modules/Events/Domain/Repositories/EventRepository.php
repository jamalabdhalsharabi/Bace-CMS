<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Events\Domain\Models\Event;

/**
 * Event Repository.
 *
 * Read-only repository for Event model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Event>
 *
 * @package Modules\Events\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventRepository extends BaseRepository
{
    /**
     * Create a new EventRepository instance.
     *
     * @param Event $model The Event model instance
     */
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated events with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['upcoming'])) {
            $query->where('starts_at', '>', now());
        }

        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        return $query->orderBy('starts_at')->paginate($perPage);
    }

    /**
     * Get upcoming events.
     */
    public function getUpcoming(int $limit = 10): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get ongoing events.
     */
    public function getOngoing(): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();
    }

    /**
     * Get past events.
     */
    public function getPast(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('ends_at', '<', now())
            ->orderByDesc('ends_at')
            ->paginate($perPage);
    }

    /**
     * Find by slug.
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Event
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }
}
