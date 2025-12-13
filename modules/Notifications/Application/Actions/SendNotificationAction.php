<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Notifications\Domain\DTO\NotificationData;
use Modules\Notifications\Domain\Models\Notification;
use Modules\Notifications\Domain\Repositories\NotificationRepository;

final class SendNotificationAction extends Action
{
    public function __construct(
        private readonly NotificationRepository $repository
    ) {}

    public function execute(NotificationData $data): Notification
    {
        return $this->repository->create([
            'user_id' => $data->user_id,
            'type' => $data->type,
            'notifiable_id' => $data->notifiable_id,
            'notifiable_type' => $data->notifiable_type,
            'data' => $data->data,
        ]);
    }

    public function sendToMany(array $userIds, string $type, array $data): int
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $this->execute(new NotificationData(
                user_id: $userId,
                type: $type,
                data: $data,
            ));
            $count++;
        }

        return $count;
    }
}
