<?php

declare(strict_types=1);

namespace Modules\Events\Application\Services;

use Modules\Events\Application\Actions\Event\CreateEventAction;
use Modules\Events\Application\Actions\Event\DeleteEventAction;
use Modules\Events\Application\Actions\Event\DuplicateEventAction;
use Modules\Events\Application\Actions\Event\PublishEventAction;
use Modules\Events\Application\Actions\Event\UpdateEventAction;
use Modules\Events\Domain\DTO\EventData;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

/**
 * Event Command Service.
 *
 * Orchestrates write operations for events.
 */
final class EventCommandService
{
    public function __construct(
        private readonly CreateEventAction $createAction,
        private readonly UpdateEventAction $updateAction,
        private readonly PublishEventAction $publishAction,
        private readonly DeleteEventAction $deleteAction,
        private readonly DuplicateEventAction $duplicateAction,
        private readonly EventRepository $repository,
    ) {}

    public function create(EventData $data): Event
    {
        return $this->createAction->execute($data);
    }

    public function update(Event $event, EventData $data): Event
    {
        return $this->updateAction->execute($event, $data);
    }

    public function publish(Event $event): Event
    {
        return $this->publishAction->execute($event);
    }

    public function unpublish(Event $event): Event
    {
        return $this->publishAction->unpublish($event);
    }

    public function cancel(Event $event): Event
    {
        return $this->publishAction->cancel($event);
    }

    public function postpone(Event $event, \DateTime $newDate): Event
    {
        $this->repository->update($event->id, [
            'status' => 'postponed',
            'start_date' => $newDate,
        ]);
        return $event->fresh();
    }

    public function delete(Event $event): bool
    {
        return $this->deleteAction->execute($event);
    }

    public function duplicate(Event $event): Event
    {
        return $this->duplicateAction->execute($event);
    }
}
