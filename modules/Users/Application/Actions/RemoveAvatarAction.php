<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

final class RemoveAvatarAction extends Action
{
    public function execute(User $user): User
    {
        if ($user->profile?->avatar) {
            $disk = config('users.avatars.disk', 'public');
            Storage::disk($disk)->delete($user->profile->avatar);
            $user->profile->update(['avatar' => null]);
        }

        return $user->fresh(['profile']);
    }
}
