<?php

declare(strict_types=1);

namespace Modules\Events\Application\Services;

use Modules\Events\Application\Actions\Event\CreateEventAction;
use Modules\Events\Application\Actions\Event\DeleteEventAction;
use Modules\Events\Application\Actions\Event\DuplicateEventAction;
use Modules\Events\Application\Actions\Event\PostponeEventAction;
use Modules\Events\Application\Actions\Event\PublishEventAction;
use Modules\Events\Application\Actions\Event\UpdateEventAction;
use Modules\Events\Domain\DTO\EventData;
use Modules\Events\Domain\Models\Event;

/**
 * Event Command Service.
 *
 * Orchestrates all write operations for events via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Events\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventCommandService
{
    /**
     * Create a new EventCommandService instance.
     *
     * @param CreateEventAction $createAction Action for creating events
     * @param UpdateEventAction $updateAction Action for updating events
     * @param PublishEventAction $publishAction Action for publishing events
     * @param DeleteEventAction $deleteAction Action for deleting events
     * @param DuplicateEventAction $duplicateAction Action for duplicating events
     * @param PostponeEventAction $postponeAction Action for postponing events
     */
    public function __construct(
        private readonly CreateEventAction $createAction,
        private readonly UpdateEventAction $updateAction,
        private readonly PublishEventAction $publishAction,
        private readonly DeleteEventAction $deleteAction,
        private readonly DuplicateEventAction $duplicateAction,
        private readonly PostponeEventAction $postponeAction,
    ) {}

    /**
     * Create a new event.
     *
     * @param EventData $data The event data DTO
     *
     * @return Event The created event
     */
    public function create(EventData $data): Event
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing event.
     *
     * @param Event $event The event to update
     * @param EventData $data The updated event data
     *
     * @return Event The updated event
     */
    public function update(Event $event, EventData $data): Event
    {
        return $this->updateAction->execute($event, $data);
    }

    /**
     * Publish an event.
     *
     * @param Event $event The event to publish
     *
     * @return Event The published event
     */
    public function publish(Event $event): Event
    {
        return $this->publishAction->execute($event);
    }

    /**
     * Unpublish an event.
     *
     * @param Event $event The event to unpublish
     *
     * @return Event The unpublished event
     */
    public function unpublish(Event $event): Event
    {
        return $this->publishAction->unpublish($event);
    }

    /**
     * Cancel an event.
     *
     * @param Event $event The event to cancel
     *
     * @return Event The cancelled event
     */
    public function cancel(Event $event): Event
    {
        return $this->publishAction->cancel($event);
    }

    /**
     * Postpone an event to a new date.
     *
     * @param Event $event The event to postpone
     * @param \DateTime $newDate The new start date
     *
     * @return Event The postponed event
     */
    public function postpone(Event $event, \DateTime $newDate): Event
    {
        return $this->postponeAction->execute($event, $newDate);
    }

    /**
     * Delete an event.
     *
     * @param Event $event The event to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Event $event): bool
    {
        return $this->deleteAction->execute($event);
    }

    /**
     * Duplicate an event.
     *
     * @param Event $event The event to duplicate
     *
     * @return Event The duplicated event
     */
    public function duplicate(Event $event): Event
    {
        return $this->duplicateAction->execute($event);
    }
}
