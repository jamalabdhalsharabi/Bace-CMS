<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Users\Domain\Models\User;

/**
 * User Registered Event.
 */
final class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
