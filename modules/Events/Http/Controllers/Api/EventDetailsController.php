<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Http\Requests\AddAgendaItemRequest;
use Modules\Events\Http\Requests\AddSpeakerRequest;
use Modules\Events\Http\Requests\SetCapacityRequest;
use Modules\Events\Http\Requests\SetOnlineDetailsRequest;
use Modules\Events\Http\Requests\SetVenueRequest;
use Modules\Events\Http\Resources\EventResource;

/**
 * Event Details Controller.
 *
 * Handles event details operations including speakers, agenda,
 * venue, online details, and capacity settings.
 *
 * @package Modules\Events\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventDetailsController extends BaseController
{
    /**
     * Create a new EventDetailsController instance.
     *
     * @param EventQueryService $queryService Service for event read operations
     * @param EventCommandService $commandService Service for event write operations
     */
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService
    ) {}

    /**
     * Add speaker.
     *
     * @param AddSpeakerRequest $request The validated speaker request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The created speaker (HTTP 201) or 404 error
     */
    public function addSpeaker(AddSpeakerRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $speaker = $this->commandService->addSpeaker($event, $request->validated());

            return $this->created($speaker, 'Speaker added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add speaker: ' . $e->getMessage());
        }
    }

    /**
     * Remove speaker.
     *
     * @param string $id The UUID of the event
     * @param string $speakerId The UUID of the speaker
     *
     * @return JsonResponse Success message or 404 error
     */
    public function removeSpeaker(string $id, string $speakerId): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->removeSpeaker($event, $speakerId);

            return $this->success(null, 'Speaker removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove speaker: ' . $e->getMessage());
        }
    }

    /**
     * Add agenda item.
     *
     * @param AddAgendaItemRequest $request The validated agenda request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The created agenda item (HTTP 201) or 404 error
     */
    public function addAgendaItem(AddAgendaItemRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $item = $this->commandService->addAgendaItem($event, $request->validated());

            return $this->created($item, 'Agenda item added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add agenda item: ' . $e->getMessage());
        }
    }

    /**
     * Update agenda item.
     *
     * @param Request $request The request containing update data
     * @param string $id The UUID of the event
     * @param string $itemId The UUID of the agenda item
     *
     * @return JsonResponse The updated agenda item or 404 error
     */
    public function updateAgendaItem(Request $request, string $id, string $itemId): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $item = $this->commandService->updateAgendaItem($event, $itemId, $request->all());

            return $this->success($item, 'Agenda item updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update agenda item: ' . $e->getMessage());
        }
    }

    /**
     * Remove agenda item.
     *
     * @param string $id The UUID of the event
     * @param string $itemId The UUID of the agenda item
     *
     * @return JsonResponse Success message or 404 error
     */
    public function removeAgendaItem(string $id, string $itemId): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->removeAgendaItem($event, $itemId);

            return $this->success(null, 'Agenda item removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove agenda item: ' . $e->getMessage());
        }
    }

    /**
     * Set venue.
     *
     * @param SetVenueRequest $request The validated venue request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The updated event or 404 error
     */
    public function setVenue(SetVenueRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->setVenue($event, $request->validated());

            return $this->success(new EventResource($event), 'Venue set');
        } catch (\Throwable $e) {
            return $this->error('Failed to set venue: ' . $e->getMessage());
        }
    }

    /**
     * Set online event details.
     *
     * @param SetOnlineDetailsRequest $request The validated online details request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The updated event or 404 error
     */
    public function setOnlineDetails(SetOnlineDetailsRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->setOnlineDetails($event, $request->validated());

            return $this->success(new EventResource($event), 'Online details set');
        } catch (\Throwable $e) {
            return $this->error('Failed to set online details: ' . $e->getMessage());
        }
    }

    /**
     * Set capacity.
     *
     * @param SetCapacityRequest $request The validated capacity request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The updated event or 404 error
     */
    public function setCapacity(SetCapacityRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->setCapacity($event, $request->capacity);

            return $this->success(new EventResource($event), 'Capacity set');
        } catch (\Throwable $e) {
            return $this->error('Failed to set capacity: ' . $e->getMessage());
        }
    }

    /**
     * Enable waiting list.
     *
     * @param string $id The UUID of the event
     *
     * @return JsonResponse The updated event or 404 error
     */
    public function enableWaitingList(string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $event = $this->commandService->enableWaitingList($event);

            return $this->success(new EventResource($event), 'Waiting list enabled');
        } catch (\Throwable $e) {
            return $this->error('Failed to enable waiting list: ' . $e->getMessage());
        }
    }
}
