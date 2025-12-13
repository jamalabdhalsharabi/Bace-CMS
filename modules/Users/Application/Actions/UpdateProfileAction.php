<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

final class UpdateProfileAction extends Action
{
    public function execute(User $user, array $data): User
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $user->fresh(['profile']);
    }
}
