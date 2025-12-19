<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Http\Requests\CreateEventRequest;
use Modules\Events\Http\Requests\DuplicateEventRequest;
use Modules\Events\Http\Requests\PostponeEventRequest;
use Modules\Events\Http\Requests\ScheduleEventRequest;
use Modules\Events\Http\Requests\CreateRecurringEventRequest;
use Modules\Events\Http\Resources\EventResource;

/**
 * Event Management Controller.
 *
 * Handles CRUD and workflow operations for events.
 *
 * @package Modules\Events\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventManagementController extends BaseController
{
    /**
     * Create a new EventManagementController instance.
     *
     * @param EventQueryService $queryService Service for event read operations
     * @param EventCommandService $commandService Service for event write operations
     */
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService
    ) {}

    /**
     * Store a newly created event.
     *
     * @param CreateEventRequest $request The validated request
     *
     * @return JsonResponse The created event (HTTP 201)
     */
    public function store(CreateEventRequest $request): JsonResponse
    {
        try {
            $event = $this->commandService->create($request->validated());

            return $this->created(new EventResource($event), 'Event created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified event.
     *
     * @param Request $request The request containing update data
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The updated event or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->update($event, $request->all());

            return $this->success(new EventResource($event), 'Event updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update event: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified event.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->delete($event);

            return $this->success(null, 'Event deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete event: ' . $e->getMessage());
        }
    }

    /**
     * Publish the event.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The published event or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->publish($event);

            return $this->success(new EventResource($event), 'Event published');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish event: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish the event.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The unpublished event or 404 error
     */
    public function unpublish(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->unpublish($event);

            return $this->success(new EventResource($event), 'Event unpublished');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish event: ' . $e->getMessage());
        }
    }

    /**
     * Schedule the event publication.
     *
     * @param ScheduleEventRequest $request The validated schedule request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The scheduled event or 404 error
     */
    public function schedule(ScheduleEventRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->schedule($event, new \DateTime($request->scheduled_at));

            return $this->success(new EventResource($event), 'Event scheduled');
        } catch (\Throwable $e) {
            return $this->error('Failed to schedule event: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the event.
     *
     * @param Request $request The request containing cancellation reason
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The cancelled event or 404 error
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->cancel($event, $request->reason);

            return $this->success(new EventResource($event), 'Event cancelled');
        } catch (\Throwable $e) {
            return $this->error('Failed to cancel event: ' . $e->getMessage());
        }
    }

    /**
     * Postpone the event.
     *
     * @param PostponeEventRequest $request The validated postpone request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The postponed event or 404 error
     */
    public function postpone(PostponeEventRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->postpone($event, $request->new_start_date, $request->new_end_date);

            return $this->success(new EventResource($event), 'Event postponed');
        } catch (\Throwable $e) {
            return $this->error('Failed to postpone event: ' . $e->getMessage());
        }
    }

    /**
     * Feature the event.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The featured event or 404 error
     */
    public function feature(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->feature($event);

            return $this->success(new EventResource($event), 'Event featured');
        } catch (\Throwable $e) {
            return $this->error('Failed to feature event: ' . $e->getMessage());
        }
    }

    /**
     * Unfeature the event.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The unfeatured event or 404 error
     */
    public function unfeature(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->unfeature($event);

            return $this->success(new EventResource($event), 'Event unfeatured');
        } catch (\Throwable $e) {
            return $this->error('Failed to unfeature event: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate the event.
     *
     * @param DuplicateEventRequest $request The validated duplicate request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The duplicated event (HTTP 201) or 404 error
     */
    public function duplicate(DuplicateEventRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $clone = $this->commandService->duplicate($event, $request->new_slug);

            return $this->created(new EventResource($clone), 'Event duplicated');
        } catch (\Throwable $e) {
            return $this->error('Failed to duplicate event: ' . $e->getMessage());
        }
    }

    /**
     * Create recurring event.
     *
     * @param CreateRecurringEventRequest $request The validated recurring request
     *
     * @return JsonResponse The created events (HTTP 201) or 404 error
     */
    public function createRecurring(CreateRecurringEventRequest $request): JsonResponse
    {
        try {
            $baseEvent = $this->queryService->find($request->base_event_id);

            if (!$baseEvent) {
                return $this->notFound('Event not found');
            }

            $events = $this->commandService->createRecurring(
                $baseEvent,
                $request->recurrence_pattern,
                $request->occurrences
            );

            return $this->created(EventResource::collection($events), 'Recurring events created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create recurring events: ' . $e->getMessage());
        }
    }
}
