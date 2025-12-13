<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Events\UserStatusChanged;
use Modules\Users\Domain\Models\User;

final class SuspendUserAction extends Action
{
    public function execute(User $user): User
    {
        $previousStatus = $user->status;
        $user->suspend();

        event(new UserStatusChanged($user, $previousStatus, 'suspended'));

        return $user;
    }
}
