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
 *
 * Orchestrates all notification write operations via Action classes.
 * Handles sending, marking as read, and deleting notifications.
 *
 * @package Modules\Notifications\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class NotificationCommandService
{
    /**
     * Create a new NotificationCommandService instance.
     *
     * @param SendNotificationAction $sendAction Action for sending notifications
     * @param MarkNotificationReadAction $markReadAction Action for marking as read
     * @param DeleteNotificationAction $deleteAction Action for deleting notifications
     */
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
