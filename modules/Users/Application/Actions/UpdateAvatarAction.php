<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

final class UpdateAvatarAction extends Action
{
    public function execute(User $user, UploadedFile $file): User
    {
        $disk = config('users.avatars.disk', 'public');
        $path = config('users.avatars.path', 'avatars');

        if ($user->profile?->avatar) {
            Storage::disk($disk)->delete($user->profile->avatar);
        }

        $avatarPath = $file->store($path, $disk);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar' => $avatarPath]
        );

        return $user->fresh(['profile']);
    }
}
