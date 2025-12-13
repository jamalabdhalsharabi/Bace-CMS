<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Application\Services\EventRegistrationService;
use Modules\Events\Domain\DTO\EventData;
use Modules\Events\Http\Requests\CreateEventRequest;
use Modules\Events\Http\Requests\UpdateEventRequest;
use Modules\Events\Http\Resources\EventResource;

/**
 * Event Controller V2.
 *
 * Uses Clean Architecture with specialized services.
 */
final class EventControllerV2 extends BaseController
{
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService,
        private readonly EventRegistrationService $registrationService,
    ) {}

    /**
     * List events with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $events = $this->queryService->list(
            filters: $request->only(['status', 'featured', 'upcoming', 'event_type']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(EventResource::collection($events)->resource);
    }

    /**
     * Show a single event.
     */
    public function show(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        return $this->success(new EventResource($event));
    }

    /**
     * Show event by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $event = $this->queryService->findBySlug($slug);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        return $this->success(new EventResource($event));
    }

    /**
     * Create a new event.
     */
    public function store(CreateEventRequest $request): JsonResponse
    {
        $data = EventData::fromRequest($request);
        $event = $this->commandService->create($data);

        return $this->created(new EventResource($event), 'Event created');
    }

    /**
     * Update an event.
     */
    public function update(UpdateEventRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $data = EventData::fromRequest($request);
        $event = $this->commandService->update($event, $data);

        return $this->success(new EventResource($event), 'Event updated');
    }

    /**
     * Delete an event.
     */
    public function destroy(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $this->commandService->delete($event);

        return $this->success(null, 'Event deleted');
    }

    /**
     * Publish an event.
     */
    public function publish(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $event = $this->commandService->publish($event);

        return $this->success(new EventResource($event), 'Event published');
    }

    /**
     * Unpublish an event.
     */
    public function unpublish(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $event = $this->commandService->unpublish($event);

        return $this->success(new EventResource($event), 'Event unpublished');
    }

    /**
     * Cancel an event.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $event = $this->commandService->cancel($event, $request->string('reason'));

        return $this->success(new EventResource($event), 'Event cancelled');
    }

    /**
     * Postpone an event.
     */
    public function postpone(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_date' => 'required|date|after:now']);

        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $event = $this->commandService->postpone($event, new \DateTime($request->new_date));

        return $this->success(new EventResource($event), 'Event postponed');
    }

    /**
     * Duplicate an event.
     */
    public function duplicate(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $clone = $this->commandService->duplicate($event);

        return $this->created(new EventResource($clone), 'Event duplicated');
    }

    /**
     * Get upcoming events.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $events = $this->queryService->getUpcoming($request->integer('limit', 10));

        return $this->success(EventResource::collection($events));
    }

    /**
     * Get ongoing events.
     */
    public function ongoing(): JsonResponse
    {
        $events = $this->queryService->getOngoing();

        return $this->success(EventResource::collection($events));
    }

    /**
     * Get past events.
     */
    public function past(Request $request): JsonResponse
    {
        $events = $this->queryService->getPast($request->integer('per_page', 15));

        return $this->paginated(EventResource::collection($events)->resource);
    }

    /**
     * Register for an event.
     */
    public function register(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'attendee_name' => 'required|string|max:255',
            'attendee_email' => 'required|email|max:255',
            'attendee_phone' => 'nullable|string|max:50',
            'ticket_type_id' => 'nullable|uuid|exists:event_ticket_types,id',
            'quantity' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        if (!$this->registrationService->hasAvailableSpots($event)) {
            return $this->error('Event is fully booked', 422);
        }

        $registration = $this->registrationService->register($event, $request->validated());

        return $this->created($registration, 'Registration successful');
    }

    /**
     * Cancel a registration.
     */
    public function cancelRegistration(Request $request, string $id, string $registrationId): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $registration = $event->registrations()->find($registrationId);

        if (!$registration) {
            return $this->notFound('Registration not found');
        }

        $registration = $this->registrationService->cancel($registration, $request->string('reason'));

        return $this->success($registration, 'Registration cancelled');
    }

    /**
     * Check-in an attendee.
     */
    public function checkIn(string $id, string $registrationId): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $registration = $event->registrations()->find($registrationId);

        if (!$registration) {
            return $this->notFound('Registration not found');
        }

        $registration = $this->registrationService->checkIn($registration);

        return $this->success($registration, 'Check-in successful');
    }

    /**
     * Get event registrations.
     */
    public function registrations(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        return $this->success($event->registrations);
    }

    /**
     * Get registration statistics.
     */
    public function registrationStats(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);

        if (!$event) {
            return $this->notFound('Event not found');
        }

        $stats = [
            'total_registrations' => $this->registrationService->getRegistrationCount($event),
            'max_attendees' => $event->max_attendees,
            'available_spots' => $event->max_attendees 
                ? max(0, $event->max_attendees - $this->registrationService->getRegistrationCount($event))
                : null,
            'is_fully_booked' => !$this->registrationService->hasAvailableSpots($event),
        ];

        return $this->success($stats);
    }
}
