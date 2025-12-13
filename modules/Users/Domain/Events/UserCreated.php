<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Users\Domain\Models\User;

final class UserCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
