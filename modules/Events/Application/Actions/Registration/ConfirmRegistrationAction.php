<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Registration;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Events\RegistrationConfirmed;
use Modules\Events\Domain\Models\EventRegistration;

final class ConfirmRegistrationAction extends Action
{
    public function execute(EventRegistration $registration): EventRegistration
    {
        $registration->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        event(new RegistrationConfirmed($registration));

        return $registration->fresh();
    }
}
