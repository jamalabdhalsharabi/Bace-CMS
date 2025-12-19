<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Notification Repository.
 *
 * Read-only repository for Notification model queries.
 * Write operations (markAllAsRead, deleteAllRead) should be moved to Actions.
 *
 * @extends BaseRepository<Notification>
 *
 * @package Modules\Notifications\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class NotificationRepository extends BaseRepository
{
    /**
     * Create a new NotificationRepository instance.
     *
     * @param Notification $model The Notification model instance
     */
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated notifications for a user.
     *
     * @param string $userId The user ID
     * @param array<string, mixed> $filters Available filters: unread_only, type
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Notification>
     */
    public function getForUser(string $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()->where('user_id', $userId);

        if (isset($filters['unread_only']) && $filters['unread_only']) {
            $query->whereNull('read_at');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get unread notification count for a user.
     *
     * @param string $userId The user ID
     *
     * @return int
     */
    public function getUnreadCount(string $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
