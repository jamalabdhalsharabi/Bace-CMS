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
 * @extends BaseRepository<Event>
 */
final class EventRepository extends BaseRepository
{
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
            $query->where('start_date', '>', now());
        }

        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        return $query->orderBy('start_date')->paginate($perPage);
    }

    /**
     * Get upcoming events.
     */
    public function getUpcoming(int $limit = 10): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
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
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Get past events.
     */
    public function getPast(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('end_date', '<', now())
            ->orderByDesc('end_date')
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
