<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Http\Requests\CreateEventRequest;
use Modules\Events\Http\Requests\RegisterEventRequest;
use Modules\Events\Http\Requests\ScheduleEventRequest;
use Modules\Events\Http\Requests\PostponeEventRequest;
use Modules\Events\Http\Requests\DuplicateEventRequest;
use Modules\Events\Http\Requests\CreateRecurringEventRequest;
use Modules\Events\Http\Requests\ConfirmationCodeRequest;
use Modules\Events\Http\Requests\AddSpeakerRequest;
use Modules\Events\Http\Requests\AddAgendaItemRequest;
use Modules\Events\Http\Requests\SetVenueRequest;
use Modules\Events\Http\Requests\SetOnlineDetailsRequest;
use Modules\Events\Http\Requests\SetCapacityRequest;
use Modules\Events\Http\Resources\EventResource;

class EventController extends BaseController
{
    public function __construct(
        protected EventQueryService $queryService,
        protected EventCommandService $commandService
    ) {
    }

    /** Display a paginated listing of events. */
    public function index(Request $request): JsonResponse
    {
        $events = $this->queryService->list($request->only(['status', 'upcoming', 'featured']), $request->integer('per_page', 12));
        return $this->paginated(EventResource::collection($events)->resource);
    }

    /** Display the specified event by its UUID. */
    public function show(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    /** Display the specified event by its URL slug. */
    public function showBySlug(string $slug): JsonResponse
    {
        $event = $this->queryService->findBySlug($slug);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    /** Store a newly created event. */
    public function store(CreateEventRequest $request): JsonResponse
    {
        return $this->created(new EventResource($this->commandService->create($request->validated())));
    }

    /** Update the specified event. */
    public function update(Request $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        return $this->success(new EventResource($this->commandService->update($event, $request->all())));
    }

    /** Delete the specified event. */
    public function destroy(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->delete($event);
        return $this->success(null, 'Event deleted');
    }

    /** Register a user for the event. */
    public function register(RegisterEventRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $registration = $this->commandService->register($event, $request->validated());
        return $this->created(['confirmation_code' => $registration->confirmation_code], 'Registration successful');
    }

    /** Publish the event. */
    public function publish(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->publish($event);
        return $this->success(new EventResource($event), 'Event published');
    }

    /** Unpublish the event. */
    public function unpublish(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->unpublish($event);
        return $this->success(new EventResource($event), 'Event unpublished');
    }

    /** Schedule the event publication. */
    public function schedule(ScheduleEventRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->schedule($event, new \DateTime($request->scheduled_at));
        return $this->success(new EventResource($event));
    }

    /** Cancel the event. */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->cancel($event, $request->reason);
        return $this->success(new EventResource($event), 'Event cancelled');
    }

    /** Postpone the event. */
    public function postpone(PostponeEventRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->postpone($event, $request->new_start_date, $request->new_end_date);
        return $this->success(new EventResource($event), 'Event postponed');
    }

    /** Feature the event. */
    public function feature(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->feature($event);
        return $this->success(new EventResource($event), 'Event featured');
    }

    /** Unfeature the event. */
    public function unfeature(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->unfeature($event);
        return $this->success(new EventResource($event), 'Event unfeatured');
    }

    /** Duplicate the event. */
    public function duplicate(DuplicateEventRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $clone = $this->commandService->duplicate($event, $request->new_slug);
        return $this->created(new EventResource($clone));
    }

    /** Create recurring event. */
    public function createRecurring(CreateRecurringEventRequest $request): JsonResponse
    {
        $baseEvent = $this->queryService->find($request->base_event_id);
        if (!$baseEvent) return $this->notFound('Event not found');
        $events = $this->commandService->createRecurring($baseEvent, $request->recurrence_pattern, $request->occurrences);
        return $this->created(EventResource::collection($events));
    }

    /** Cancel registration. */
    public function cancelRegistration(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->cancelRegistration($event, $request->confirmation_code);
        return $this->success(null, 'Registration cancelled');
    }

    /** Confirm attendance. */
    public function confirmAttendance(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->confirmAttendance($event, $request->confirmation_code);
        return $this->success(null, 'Attendance confirmed');
    }

    /** Check-in attendee. */
    public function checkIn(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->checkIn($event, $request->confirmation_code);
        return $this->success(null, 'Check-in successful');
    }

    /** Get registrations. */
    public function registrations(Request $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $registrations = $this->queryService->getRegistrations($event, $request->integer('per_page', 20));
        return $this->paginated($registrations);
    }

    /** Export registrations. */
    public function exportRegistrations(Request $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $result = $this->commandService->exportRegistrations($event, $request->input('format', 'csv'));
        return $this->success($result);
    }

    /** Send reminder to registrants. */
    public function sendReminder(Request $request, string $id): JsonResponse
    {
        $request->validate(['message' => 'nullable|string']);
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->sendReminder($event, $request->message);
        return $this->success(null, 'Reminders sent');
    }

    /** Add speaker. */
    public function addSpeaker(AddSpeakerRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $speaker = $this->commandService->addSpeaker($event, $request->validated());
        return $this->created($speaker);
    }

    /** Remove speaker. */
    public function removeSpeaker(string $id, string $speakerId): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->removeSpeaker($event, $speakerId);
        return $this->success(null, 'Speaker removed');
    }

    /** Add agenda item. */
    public function addAgendaItem(AddAgendaItemRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $item = $this->commandService->addAgendaItem($event, $request->validated());
        return $this->created($item);
    }

    /** Update agenda item. */
    public function updateAgendaItem(Request $request, string $id, string $itemId): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $item = $this->commandService->updateAgendaItem($event, $itemId, $request->all());
        return $this->success($item);
    }

    /** Remove agenda item. */
    public function removeAgendaItem(string $id, string $itemId): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->commandService->removeAgendaItem($event, $itemId);
        return $this->success(null, 'Agenda item removed');
    }

    /** Set venue. */
    public function setVenue(SetVenueRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->setVenue($event, $request->validated());
        return $this->success(new EventResource($event));
    }

    /** Set online event details. */
    public function setOnlineDetails(SetOnlineDetailsRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->setOnlineDetails($event, $request->validated());
        return $this->success(new EventResource($event));
    }

    /** Set capacity. */
    public function setCapacity(SetCapacityRequest $request, string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->setCapacity($event, $request->capacity);
        return $this->success(new EventResource($event));
    }

    /** Enable waiting list. */
    public function enableWaitingList(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $event = $this->commandService->enableWaitingList($event);
        return $this->success(new EventResource($event));
    }

    /** Get upcoming events. */
    public function upcoming(Request $request): JsonResponse
    {
        $events = $this->queryService->getUpcoming($request->integer('limit', 10));
        return $this->success(EventResource::collection($events));
    }

    /** Get past events. */
    public function past(Request $request): JsonResponse
    {
        $events = $this->queryService->getPast($request->integer('per_page', 12));
        return $this->paginated(EventResource::collection($events)->resource);
    }

    /** Get event statistics. */
    public function stats(string $id): JsonResponse
    {
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $stats = $this->queryService->getStats($event);
        return $this->success($stats);
    }

    /** Add to calendar. */
    public function addToCalendar(Request $request, string $id): JsonResponse
    {
        $request->validate(['format' => 'nullable|in:ics,google,outlook']);
        $event = $this->queryService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $calendarData = $this->commandService->generateCalendarData($event, $request->input('format', 'ics'));
        return $this->success($calendarData);
    }
}
