<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Models\Event;

/**
 * Postpone Event Action.
 *
 * Postpones an event to a new date.
 *
 * @package Modules\Events\Application\Actions\Event
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PostponeEventAction extends Action
{
    /**
     * Execute the postpone action.
     *
     * @param Event $event The event to postpone
     * @param \DateTime $newDate The new start date
     *
     * @return Event The updated event
     */
    public function execute(Event $event, \DateTime $newDate): Event
    {
        $event->update([
            'status' => 'postponed',
            'start_date' => $newDate,
        ]);

        return $event->fresh();
    }
}
