<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

final class ChangePasswordAction extends Action
{
    public function execute(User $user, string $password): User
    {
        $user->update(['password' => Hash::make($password)]);

        return $user;
    }
}
