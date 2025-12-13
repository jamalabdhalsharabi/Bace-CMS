<?php

declare(strict_types=1);

namespace Modules\Users\Application\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\DTO\UserData;
use Modules\Users\Domain\Events\UserCreated;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository;

/**
 * Create User Action.
 */
final class CreateUserAction extends Action
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function execute(UserData $data): User
    {
        return $this->transaction(function () use ($data) {
            $user = $this->repository->create([
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'status' => $data->status,
            ]);

            $user->profile()->create($data->toProfileArray());

            if ($data->role_ids) {
                $user->roles()->sync($data->role_ids);
            }

            event(new UserCreated($user));

            return $user->fresh(['profile', 'roles']);
        });
    }
}
