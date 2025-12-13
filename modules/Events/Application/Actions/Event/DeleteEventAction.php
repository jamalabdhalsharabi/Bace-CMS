<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

final class DeleteEventAction extends Action
{
    public function __construct(
        private readonly EventRepository $repository
    ) {}

    public function execute(Event $event): bool
    {
        $event->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($event->id);
    }
}
