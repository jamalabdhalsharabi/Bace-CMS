<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

final class RegistrationReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly EventRegistration $registration
    ) {}
}
