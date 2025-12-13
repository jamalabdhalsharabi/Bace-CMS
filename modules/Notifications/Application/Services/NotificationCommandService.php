<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Services;

use Modules\Notifications\Application\Actions\DeleteNotificationAction;
use Modules\Notifications\Application\Actions\MarkNotificationReadAction;
use Modules\Notifications\Application\Actions\SendNotificationAction;
use Modules\Notifications\Domain\DTO\NotificationData;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Notification Command Service.
 */
final class NotificationCommandService
{
    public function __construct(
        private readonly SendNotificationAction $sendAction,
        private readonly MarkNotificationReadAction $markReadAction,
        private readonly DeleteNotificationAction $deleteAction,
    ) {}

    public function send(NotificationData $data): Notification
    {
        return $this->sendAction->execute($data);
    }

    public function sendToMany(array $userIds, string $type, array $data): int
    {
        return $this->sendAction->sendToMany($userIds, $type, $data);
    }

    public function markAsRead(Notification $notification): Notification
    {
        return $this->markReadAction->execute($notification);
    }

    public function markAllAsRead(string $userId): int
    {
        return $this->markReadAction->markAllAsRead($userId);
    }

    public function delete(Notification $notification): bool
    {
        return $this->deleteAction->execute($notification);
    }

    public function deleteAllRead(string $userId): int
    {
        return $this->deleteAction->deleteAllRead($userId);
    }
}
