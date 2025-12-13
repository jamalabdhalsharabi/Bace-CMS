<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\DTO\UserData;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository;

/**
 * Update User Action.
 */
final class UpdateUserAction extends Action
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function execute(User $user, UserData $data): User
    {
        return $this->transaction(function () use ($user, $data) {
            $userData = $data->toUserArray();
            
            if (isset($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }

            if (!empty($userData)) {
                $this->repository->update($user->id, $userData);
            }

            $profileData = $data->toProfileArray();
            if (!empty($profileData)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            if ($data->role_ids !== null) {
                $user->roles()->sync($data->role_ids);
            }

            return $user->fresh(['profile', 'roles']);
        });
    }
}
