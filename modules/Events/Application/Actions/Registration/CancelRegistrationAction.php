<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Registration;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Models\EventRegistration;

final class CancelRegistrationAction extends Action
{
    public function execute(EventRegistration $registration, ?string $reason = null): EventRegistration
    {
        $meta = $registration->meta ?? [];
        if ($reason) {
            $meta['cancellation_reason'] = $reason;
        }

        $registration->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'meta' => $meta,
        ]);

        return $registration->fresh();
    }
}
