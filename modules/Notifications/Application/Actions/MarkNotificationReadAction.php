<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Notifications\Domain\Models\Notification;
use Modules\Notifications\Domain\Repositories\NotificationRepository;

final class MarkNotificationReadAction extends Action
{
    public function __construct(
        private readonly NotificationRepository $repository
    ) {}

    public function execute(Notification $notification): Notification
    {
        $notification->update(['read_at' => now()]);

        return $notification->fresh();
    }

    public function markAllAsRead(string $userId): int
    {
        return $this->repository->markAllAsRead($userId);
    }
}
