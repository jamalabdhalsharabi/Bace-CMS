<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository;

final class DeleteUserAction extends Action
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function execute(User $user): bool
    {
        if ($user->profile?->avatar) {
            $disk = config('users.avatars.disk', 'public');
            Storage::disk($disk)->delete($user->profile->avatar);
        }

        return $this->repository->delete($user->id);
    }
}
