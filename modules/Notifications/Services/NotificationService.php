<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notifications\Contracts\NotificationServiceContract;
use Modules\Notifications\Domain\Models\Notification;

class NotificationService implements NotificationServiceContract
{
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

    public function getUnreadCount(string $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    public function find(string $id): ?Notification
    {
        return Notification::find($id);
    }

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

    public function sendToMany(array $userIds, string $type, array $data): int
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $this->send($userId, $type, $data);
            $count++;
        }

        return $count;
    }

    public function markAsRead(Notification $notification): Notification
    {
        return $notification->markAsRead();
    }

    public function markAllAsRead(string $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function deleteAllRead(string $userId): int
    {
        return Notification::forUser($userId)->read()->delete();
    }
}
