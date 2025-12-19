<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Webhooks\Domain\Contracts\WebhookRepositoryInterface;
use Modules\Webhooks\Domain\Models\Webhook;

/**
 * Webhook Repository Implementation.
 *
 * Read-only repository for Webhook model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Webhook>
 * @implements WebhookRepositoryInterface
 *
 * @package Modules\Webhooks\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class WebhookRepository extends BaseRepository implements WebhookRepositoryInterface
{
    /**
     * Create a new WebhookRepository instance.
     *
     * @param Webhook $model The Webhook model instance
     */
    public function __construct(Webhook $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated webhooks with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: active, event, search
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Webhook>
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with('logs');

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (!empty($filters['event'])) {
            $query->whereJsonContains('events', $filters['event']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all active webhooks.
     *
     * @return Collection<int, Webhook>
     */
    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->with('logs')
            ->get();
    }

    /**
     * Get active webhooks subscribed to a specific event.
     *
     * @param string $event The event name
     *
     * @return Collection<int, Webhook>
     */
    public function getActiveByEvent(string $event): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where(function ($q) use ($event) {
                $q->whereJsonContains('events', $event)
                  ->orWhereJsonContains('events', '*');
            })
            ->get();
    }

    /**
     * Get webhooks with high failure counts.
     *
     * @param int $threshold Minimum failure count
     *
     * @return Collection<int, Webhook>
     */
    public function getFailingWebhooks(int $threshold = 5): Collection
    {
        return $this->query()
            ->where('failure_count', '>=', $threshold)
            ->get();
    }
}
