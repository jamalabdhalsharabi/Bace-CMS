<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Notifications\Contracts\NotificationServiceContract;
use Modules\Notifications\Http\Resources\NotificationResource;

class NotificationController extends BaseController
{
    public function __construct(
        protected NotificationServiceContract $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getForUser(
            auth()->id(),
            filters: $request->only(['unread_only', 'type']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(NotificationResource::collection($notifications)->resource);
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());

        return $this->success(['count' => $count]);
    }

    public function show(string $id): JsonResponse
    {
        $notification = $this->notificationService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        return $this->success(new NotificationResource($notification));
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = $this->notificationService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $notification = $this->notificationService->markAsRead($notification);

        return $this->success(new NotificationResource($notification));
    }

    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());

        return $this->success(['marked' => $count], 'All notifications marked as read');
    }

    public function destroy(string $id): JsonResponse
    {
        $notification = $this->notificationService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $this->notificationService->delete($notification);

        return $this->success(null, 'Notification deleted');
    }

    public function destroyAllRead(): JsonResponse
    {
        $count = $this->notificationService->deleteAllRead(auth()->id());

        return $this->success(['deleted' => $count], 'Read notifications deleted');
    }
}
