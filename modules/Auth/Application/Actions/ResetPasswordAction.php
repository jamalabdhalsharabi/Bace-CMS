<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

/**
 * Reset Password Action.
 */
final class ResetPasswordAction extends Action
{
    /**
     * Send password reset link.
     *
     * @param string $email User email
     */
    public function sendResetLink(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset user password.
     *
     * @param string $token Reset token
     * @param string $email User email
     * @param string $password New password
     * @throws ValidationException
     */
    public function reset(string $token, string $email, string $password): void
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
