<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

final class PublishEventAction extends Action
{
    public function __construct(
        private readonly EventRepository $repository
    ) {}

    public function execute(Event $event): Event
    {
        $this->repository->update($event->id, [
            'status' => 'published',
            'published_at' => $event->published_at ?? now(),
        ]);

        return $event->fresh();
    }

    public function unpublish(Event $event): Event
    {
        $this->repository->update($event->id, ['status' => 'draft']);

        return $event->fresh();
    }

    public function cancel(Event $event): Event
    {
        $this->repository->update($event->id, ['status' => 'cancelled']);

        return $event->fresh();
    }
}
