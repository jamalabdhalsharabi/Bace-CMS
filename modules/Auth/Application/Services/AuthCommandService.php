<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Actions\LoginAction;
use Modules\Auth\Application\Actions\LogoutAction;
use Modules\Auth\Application\Actions\RegisterAction;
use Modules\Auth\Application\Actions\ResetPasswordAction;
use Modules\Auth\Domain\DTO\LoginData;
use Modules\Auth\Domain\DTO\RegisterData;
use Modules\Users\Domain\Models\User;

/**
 * Auth Command Service.
 *
 * Orchestrates authentication operations.
 */
final class AuthCommandService
{
    public function __construct(
        private readonly LoginAction $loginAction,
        private readonly RegisterAction $registerAction,
        private readonly LogoutAction $logoutAction,
        private readonly ResetPasswordAction $resetPasswordAction,
    ) {}

    /**
     * Authenticate user and return token.
     */
    public function login(LoginData $data): array
    {
        return $this->loginAction->execute($data);
    }

    /**
     * Register a new user.
     */
    public function register(RegisterData $data): User
    {
        return $this->registerAction->execute($data);
    }

    /**
     * Log out a user.
     */
    public function logout(User $user, bool $allDevices = false): void
    {
        $this->logoutAction->execute($user, $allDevices);
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(string $email): void
    {
        $this->resetPasswordAction->sendResetLink($email);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(string $token, string $email, string $password): void
    {
        $this->resetPasswordAction->reset($token, $email, $password);
    }
}
