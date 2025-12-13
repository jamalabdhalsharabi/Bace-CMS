<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Notifications\Domain\Models\Notification;
use Modules\Notifications\Domain\Repositories\NotificationRepository;

final class DeleteNotificationAction extends Action
{
    public function __construct(
        private readonly NotificationRepository $repository
    ) {}

    public function execute(Notification $notification): bool
    {
        return $this->repository->delete($notification->id);
    }

    public function deleteAllRead(string $userId): int
    {
        return $this->repository->deleteAllRead($userId);
    }
}
