<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Notifications\Contracts\NotificationServiceContract;
use Modules\Notifications\Http\Resources\NotificationResource;

/**
 * Class NotificationController
 *
 * API controller for managing user notifications including
 * listing, reading, and deleting notifications.
 *
 * @package Modules\Notifications\Http\Controllers\Api
 */
class NotificationController extends BaseController
{
    /**
     * The notification service instance.
     *
     * @var NotificationServiceContract
     */
    protected NotificationServiceContract $notificationService;

    /**
     * Create a new NotificationController instance.
     *
     * @param NotificationServiceContract $notificationService The notification service
     */
    public function __construct(
        NotificationServiceContract $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * Display paginated notifications for the authenticated user.
     *
     * @param Request $request The request with optional filters
     * @return JsonResponse Paginated list of notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getForUser(
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
        $count = $this->notificationService->getUnreadCount(auth()->id());

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
        $notification = $this->notificationService->find($id);

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
        $notification = $this->notificationService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $notification = $this->notificationService->markAsRead($notification);

        return $this->success(new NotificationResource($notification));
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @return JsonResponse Count of marked notifications
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());

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
        $notification = $this->notificationService->find($id);

        if (!$notification || $notification->user_id !== auth()->id()) {
            return $this->notFound('Notification not found');
        }

        $this->notificationService->delete($notification);

        return $this->success(null, 'Notification deleted');
    }

    /**
     * Delete all read notifications for the authenticated user.
     *
     * @return JsonResponse Count of deleted notifications
     */
    public function destroyAllRead(): JsonResponse
    {
        $count = $this->notificationService->deleteAllRead(auth()->id());

        return $this->success(['deleted' => $count], 'Read notifications deleted');
    }
}
