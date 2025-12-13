<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notifications\Contracts\NotificationServiceContract;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Class NotificationService
 *
 * Service class for managing user notifications including
 * sending, reading, and deleting notifications.
 *
 * @package Modules\Notifications\Services
 */
class NotificationService implements NotificationServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function getForUser(string $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Notification::forUser($userId);

        if (isset($filters['unread_only']) && $filters['unread_only']) {
            $query->unread();
        }

        if (!empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnreadCount(string $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Notification
    {
        return Notification::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $userId, string $type, array $data, ?object $notifiable = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'notifiable_id' => $notifiable?->getKey(),
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'data' => $data,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function sendToMany(array $userIds, string $type, array $data): int
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $this->send($userId, $type, $data);
            $count++;
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsRead(Notification $notification): Notification
    {
        return $notification->markAsRead();
    }

    /**
     * {@inheritdoc}
     */
    public function markAllAsRead(string $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllRead(string $userId): int
    {
        return Notification::forUser($userId)->read()->delete();
    }
}
