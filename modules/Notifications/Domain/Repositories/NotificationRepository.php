<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Notification Repository.
 *
 * @extends BaseRepository<Notification>
 */
final class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

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

    public function getUnreadCount(string $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAllAsRead(string $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteAllRead(string $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->delete();
    }
}
