<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

/**
 * Logout Action.
 *
 * Handles user logout by revoking tokens.
 */
final class LogoutAction extends Action
{
    /**
     * Execute the logout action.
     *
     * @param User $user The user to log out
     * @param bool $allDevices Revoke all tokens
     */
    public function execute(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()->delete();
        }
    }
}
