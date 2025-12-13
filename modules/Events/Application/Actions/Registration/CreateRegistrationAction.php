<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Registration;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Events\RegistrationCreated;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

final class CreateRegistrationAction extends Action
{
    public function execute(Event $event, array $data): EventRegistration
    {
        $registration = $event->registrations()->create([
            'user_id' => $data['user_id'] ?? $this->userId(),
            'ticket_type_id' => $data['ticket_type_id'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'attendee_info' => $data['attendee_info'] ?? [],
            'status' => 'pending',
        ]);

        event(new RegistrationCreated($registration));

        return $registration;
    }
}
