<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Contracts\AuthServiceContract;
use Modules\Auth\Domain\Models\Role;
use Modules\Users\Domain\Models\User;

/**
 * Class AuthService
 *
 * Service class for handling authentication operations including
 * login, registration, password reset, and email verification.
 *
 * @package Modules\Auth\Services
 */
class AuthService implements AuthServiceContract
{
    /**
     * Authenticate a user and return access token.
     *
     * @param string $email User email
     * @param string $password User password
     *
     * @return array User data and token
     *
     * @throws ValidationException If credentials are invalid
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $user->recordLogin();

        $token = $user->createToken('auth-token');

        return [
            'user' => $user->load('profile'),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Register a new user.
     *
     * @param array $data Registration data: email, password, first_name, last_name
     *
     * @return User The created user
     */
    public function register(array $data): User
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->profile()->create([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
        ]);

        $defaultRole = Role::findBySlug(config('auth.default_role', 'user'));
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        event(new Registered($user));

        return $user->fresh(['profile', 'roles']);
    }

    /**
     * Log out a user by revoking their current token.
     *
     * @param User $user The user to log out
     *
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Refresh an access token.
     *
     * @param string $refreshToken The refresh token
     *
     * @return array New token data
     *
     * @throws \RuntimeException Not implemented
     */
    public function refreshToken(string $refreshToken): array
    {
        // Implementation depends on your token strategy
        throw new \RuntimeException('Refresh token not implemented');
    }

    /**
     * Send a password reset link.
     *
     * @param string $email User email
     *
     * @return void
     */
    public function forgotPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset a user's password.
     *
     * @param string $token Reset token
     * @param string $email User email
     * @param string $password New password
     *
     * @return void
     *
     * @throws ValidationException If reset fails
     */
    public function resetPassword(string $token, string $email, string $password): void
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

    /**
     * Verify a user's email address.
     *
     * @param string $id User ID
     * @param string $hash Verification hash
     *
     * @return void
     *
     * @throws ValidationException If verification fails
     */
    public function verifyEmail(string $id, string $hash): void
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid verification link.'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
    }

    /**
     * Resend email verification notification.
     *
     * @param User $user The user
     *
     * @return void
     *
     * @throws ValidationException If already verified
     */
    public function resendVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email already verified.'],
            ]);
        }

        $user->sendEmailVerificationNotification();
    }
}
