<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Http\Resources\EventResource;

/**
 * Event Listing Controller.
 *
 * Handles all read-only operations for events including listing,
 * viewing, and retrieving event data.
 *
 * @package Modules\Events\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventListingController extends BaseController
{
    /**
     * Create a new EventListingController instance.
     *
     * @param EventQueryService $queryService Service for event read operations
     */
    public function __construct(
        private readonly EventQueryService $queryService
    ) {}

    /**
     * Display a paginated listing of events.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Paginated list of events
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $events = $this->queryService->list(
                $request->only(['status', 'upcoming', 'featured']),
                $request->integer('per_page', 12)
            );

            return $this->paginated(EventResource::collection($events)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve events: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event by its UUID.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The event data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            return $event
                ? $this->success(new EventResource($event))
                : $this->notFound('Event not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve event: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event by its URL slug.
     *
     * @param string $slug The URL-friendly slug
     *
     * @return JsonResponse The event data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $event = $this->queryService->findBySlug($slug);

            return $event
                ? $this->success(new EventResource($event))
                : $this->notFound('Event not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve event: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming events.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Collection of upcoming events
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $events = $this->queryService->getUpcoming($request->integer('limit', 10));

            return $this->success(EventResource::collection($events));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve upcoming events: ' . $e->getMessage());
        }
    }

    /**
     * Get past events.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Paginated list of past events
     */
    public function past(Request $request): JsonResponse
    {
        try {
            $events = $this->queryService->getPast($request->integer('per_page', 12));

            return $this->paginated(EventResource::collection($events)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve past events: ' . $e->getMessage());
        }
    }

    /**
     * Get event statistics.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Event statistics or 404 error
     */
    public function stats(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $stats = $this->queryService->getStats($event);

            return $this->success($stats);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve stats: ' . $e->getMessage());
        }
    }
}
