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

class EventController extends BaseController
{
    public function __construct(protected EventServiceContract $eventService) {}

    public function index(Request $request): JsonResponse
    {
        $events = $this->eventService->list($request->only(['status', 'upcoming', 'featured']), $request->integer('per_page', 12));
        return $this->paginated(EventResource::collection($events)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $event = $this->eventService->findBySlug($slug);
        return $event ? $this->success(new EventResource($event)) : $this->notFound('Event not found');
    }

    public function store(CreateEventRequest $request): JsonResponse
    {
        return $this->created(new EventResource($this->eventService->create($request->validated())));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        return $this->success(new EventResource($this->eventService->update($event, $request->all())));
    }

    public function destroy(string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $this->eventService->delete($event);
        return $this->success(null, 'Event deleted');
    }

    public function register(RegisterEventRequest $request, string $id): JsonResponse
    {
        $event = $this->eventService->find($id);
        if (!$event) return $this->notFound('Event not found');
        $registration = $this->eventService->register($event, $request->validated());
        return $this->created(['confirmation_code' => $registration->confirmation_code], 'Registration successful');
    }
}
