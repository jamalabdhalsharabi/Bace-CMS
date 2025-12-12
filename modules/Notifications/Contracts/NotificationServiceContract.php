<?php

declare(strict_types=1);

namespace Modules\Notifications\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notifications\Domain\Models\Notification;

interface NotificationServiceContract
{
    public function getForUser(string $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function getUnreadCount(string $userId): int;

    public function find(string $id): ?Notification;

    public function send(string $userId, string $type, array $data, ?object $notifiable = null): Notification;

    public function sendToMany(array $userIds, string $type, array $data): int;

    public function markAsRead(Notification $notification): Notification;

    public function markAllAsRead(string $userId): int;

    public function delete(Notification $notification): bool;

    public function deleteAllRead(string $userId): int;
}
