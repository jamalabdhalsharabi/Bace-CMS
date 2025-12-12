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

class AuthService implements AuthServiceContract
{
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

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function refreshToken(string $refreshToken): array
    {
        // Implementation depends on your token strategy
        throw new \RuntimeException('Refresh token not implemented');
    }

    public function forgotPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

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
