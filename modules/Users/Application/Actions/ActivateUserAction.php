<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Events\UserStatusChanged;
use Modules\Users\Domain\Models\User;

final class ActivateUserAction extends Action
{
    public function execute(User $user): User
    {
        $previousStatus = $user->status;
        $user->activate();

        event(new UserStatusChanged($user, $previousStatus, 'active'));

        return $user;
    }
}
