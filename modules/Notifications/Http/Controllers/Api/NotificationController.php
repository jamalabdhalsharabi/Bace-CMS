<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Notifications\Application\Services\NotificationCommandService;
use Modules\Notifications\Application\Services\NotificationQueryService;
use Modules\Notifications\Http\Resources\NotificationResource;

class NotificationController extends BaseController
{
    public function __construct(
        protected NotificationQueryService $queryService,
        protected NotificationCommandService $commandService
    ) {
    }

    /**
     * Display paginated notifications for the authenticated user.
     *
     * @param Request $request The request with optional filters
     * @return JsonResponse Paginated list of notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->queryService->getForUser(
            auth()->id(),
            filters: $request->only(['unread_only', 'type']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(NotificationResource::collection($notifications)->resource);
    }

    /**
     * Get the count of unread notifications.
     *
     * @return JsonResponse The unread count
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->queryService->getUnreadCount(auth()->id());

        return $this->success(['count' => $count]);
    }

    /**
     * Display the specified notification.
     *
     * @param string $id The UUID of the notification
     * @return JsonResponse The notification or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $notification = $this->queryService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        return $this->success(new NotificationResource($notification));
    }

    /**
     * Mark a notification as read.
     *
     * @param string $id The UUID of the notification
     * @return JsonResponse The updated notification or 404 error
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = $this->queryService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $notification = $this->commandService->markAsRead($notification);

        return $this->success(new NotificationResource($notification));
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @return JsonResponse Count of marked notifications
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->commandService->markAllAsRead(auth()->id());

        return $this->success(['marked' => $count], 'All notifications marked as read');
    }

    /**
     * Delete a notification.
     *
     * @param string $id The UUID of the notification
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = $this->queryService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $this->commandService->delete($notification);

        return $this->success(null, 'Notification deleted');
    }

    /**
     * Delete all read notifications for the authenticated user.
     *
     * @return JsonResponse Count of deleted notifications
     */
    public function destroyAllRead(): JsonResponse
    {
        $count = $this->commandService->deleteAllRead(auth()->id());

        return $this->success(['deleted' => $count], 'Read notifications deleted');
    }
}
