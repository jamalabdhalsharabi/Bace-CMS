<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notifications\Domain\Models\Notification;
use Modules\Notifications\Domain\Repositories\NotificationRepository;

/**
 * Notification Query Service.
 */
final class NotificationQueryService
{
    public function __construct(
        private readonly NotificationRepository $repository
    ) {}

    public function getForUser(string $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getForUser($userId, $filters, $perPage);
    }

    public function find(string $id): ?Notification
    {
        return $this->repository->find($id);
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->repository->getUnreadCount($userId);
    }
}
