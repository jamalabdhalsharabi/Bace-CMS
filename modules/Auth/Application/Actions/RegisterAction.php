<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Actions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Domain\DTO\RegisterData;
use Modules\Auth\Domain\Events\UserRegistered;
use Modules\Auth\Domain\Models\Role;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

/**
 * Register Action.
 *
 * Handles user registration with profile creation.
 */
final class RegisterAction extends Action
{
    /**
     * Execute the registration action.
     *
     * @param RegisterData $data Registration data
     * @return User The created user
     */
    public function execute(RegisterData $data): User
    {
        return $this->transaction(function () use ($data) {
            $user = User::create([
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'status' => 'active',
            ]);

            $user->profile()->create([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'phone' => $data->phone,
            ]);

            $this->assignDefaultRole($user);

            event(new Registered($user));
            event(new UserRegistered($user));

            return $user->fresh(['profile', 'roles']);
        });
    }

    /**
     * Assign the default role to a new user.
     */
    private function assignDefaultRole(User $user): void
    {
        $defaultRole = Role::findBySlug(config('auth.default_role', 'user'));
        
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }
    }
}
