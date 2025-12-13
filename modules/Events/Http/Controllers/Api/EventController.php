<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Contracts\EventServiceContract;
use Modules\Events\Http\Requests\CreateEventRequest;
use Modules\Events\Http\Requests\RegisterEventRequest;
use Modules\Events\Http\Resources\EventResource;

/**
 * Class EventController
 * 
 * API controller for managing events including CRUD,
 * workflow, registration, and scheduling.
 * 
 * @package Modules\Events\Http\Controllers\Api
 */
class EventController extends BaseController
{
    /**
     * The event service instance for handling event-related business logic.
     *
     * @var EventServiceContract
     */
    protected EventServiceContract $eventService;

    /**
     * Create a new EventController instance.
     *
     * @param EventServiceContract $eventService The event service contract implementation
     */
    public function __construct(EventServiceContract $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a paginated listing of events.
     *
     * Supports filtering by status, upcoming events, and featured flag.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of events wrapped in EventResource
     */
    public function index(Request $request): JsonResponse
    {
        $events = $this->eventService->list($request->only(['status', 'upcoming', 'featured']), $request->integer('per_page', 12));
        return $this->paginated(EventResource::collection($events)->resource);
    }

    /**
     * Display the specified event by its UUID.
     *
     * @param string $id The UUID of the event to retrieve
     * @return JsonResponse The event data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    /**
     * Display the specified event by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the event
     * @return JsonResponse The event data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $event = $this->eventService->findBySlug($slug);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    /**
     * Store a newly created event in the database.
     *
     * @param CreateEventRequest $request The validated request containing event data
     * @return JsonResponse The newly created event (HTTP 201)
     */
    public function store(CreateEventRequest $request): JsonResponse
    {
        return $this->created(new EventResource($this->eventService->create($request->validated())));
    }

    /**
     * Update the specified event in the database.
     *
     * @param Request $request The request containing updated event data
     * @param string $id The UUID of the event to update
     * @return JsonResponse The updated event or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        return $this->success(new EventResource($this->eventService->update($event, $request->all())));
    }

    /**
     * Delete the specified event.
     *
     * @param string $id The UUID of the event to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->eventService->delete($event);
        return $this->success(null, 'Event deleted');
    }

    /**
     * Register a user for the specified event.
     *
     * Creates a new event registration and returns a confirmation code.
     *
     * @param RegisterEventRequest $request The validated registration request
     * @param string $id The UUID of the event to register for
     * @return JsonResponse Registration confirmation or 404 error
     */
    public function register(RegisterEventRequest $request, string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $registration = $this->eventService->register($event, $request->validated());
        return $this->created(['confirmation_code' => $registration->confirmation_code], 'Registration successful');
    }
}
