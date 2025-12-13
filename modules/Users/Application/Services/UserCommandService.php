<?php

declare(strict_types=1);

namespace Modules\Users\Application\Services;

use Illuminate\Http\UploadedFile;
use Modules\Users\Application\Actions\ActivateUserAction;
use Modules\Users\Application\Actions\ChangePasswordAction;
use Modules\Users\Application\Actions\CreateUserAction;
use Modules\Users\Application\Actions\DeleteUserAction;
use Modules\Users\Application\Actions\RemoveAvatarAction;
use Modules\Users\Application\Actions\SuspendUserAction;
use Modules\Users\Application\Actions\UpdateAvatarAction;
use Modules\Users\Application\Actions\UpdateProfileAction;
use Modules\Users\Application\Actions\UpdateUserAction;
use Modules\Users\Domain\DTO\UserData;
use Modules\Users\Domain\Models\User;

/**
 * User Command Service.
 */
final class UserCommandService
{
    public function __construct(
        private readonly CreateUserAction $createAction,
        private readonly UpdateUserAction $updateAction,
        private readonly DeleteUserAction $deleteAction,
        private readonly ActivateUserAction $activateAction,
        private readonly SuspendUserAction $suspendAction,
        private readonly ChangePasswordAction $changePasswordAction,
        private readonly UpdateAvatarAction $updateAvatarAction,
        private readonly RemoveAvatarAction $removeAvatarAction,
        private readonly UpdateProfileAction $updateProfileAction,
    ) {}

    public function create(UserData $data): User
    {
        return $this->createAction->execute($data);
    }

    public function update(User $user, UserData $data): User
    {
        return $this->updateAction->execute($user, $data);
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->updateProfileAction->execute($user, $data);
    }

    public function updateAvatar(User $user, UploadedFile $file): User
    {
        return $this->updateAvatarAction->execute($user, $file);
    }

    public function removeAvatar(User $user): User
    {
        return $this->removeAvatarAction->execute($user);
    }

    public function changePassword(User $user, string $password): User
    {
        return $this->changePasswordAction->execute($user, $password);
    }

    public function activate(User $user): User
    {
        return $this->activateAction->execute($user);
    }

    public function suspend(User $user): User
    {
        return $this->suspendAction->execute($user);
    }

    public function delete(User $user): bool
    {
        return $this->deleteAction->execute($user);
    }
}
