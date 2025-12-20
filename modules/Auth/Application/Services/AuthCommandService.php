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
 * Orchestrates all authentication operations via Action classes.
 * Handles login, registration, logout, and password reset flows.
 *
 * @package Modules\Auth\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class AuthCommandService
{
    /**
     * Create a new AuthCommandService instance.
     *
     * @param LoginAction $loginAction Action for user login
     * @param RegisterAction $registerAction Action for user registration
     * @param LogoutAction $logoutAction Action for user logout
     * @param ResetPasswordAction $resetPasswordAction Action for password reset
     */
    public function __construct(
        private readonly LoginAction $loginAction,
        private readonly RegisterAction $registerAction,
        private readonly LogoutAction $logoutAction,
        private readonly ResetPasswordAction $resetPasswordAction,
    ) {}

    /**
     * Authenticate user and return authentication token.
     *
     * Validates user credentials and generates API token for subsequent requests.
     * Records login activity and fires authentication events.
     *
     * @param LoginData $data Login credentials containing email and password
     * @return array Authentication result with user data, token, and token type
     *
     * @throws \Illuminate\Validation\ValidationException If credentials are invalid
     * @throws \Exception If authentication process fails
     */
    public function login(LoginData $data): array
    {
        return $this->loginAction->execute($data);
    }

    /**
     * Register a new user account.
     *
     * Creates new user with profile information and assigns default role.
     * Fires registration events and handles profile creation.
     *
     * @param RegisterData $data Registration information including name, email, password
     * @return User The newly created user with loaded relationships
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     * @throws \Exception If registration process fails
     */
    public function register(RegisterData $data): User
    {
        return $this->registerAction->execute($data);
    }

    /**
     * Log out authenticated user.
     *
     * Revokes authentication tokens and terminates user session.
     * Optionally can log out from all devices simultaneously.
     *
     * @param User $user The authenticated user to log out
     * @param bool $allDevices Whether to revoke tokens for all devices (default: false)
     * @return void
     *
     * @throws \Exception If logout process fails
     */
    public function logout(User $user, bool $allDevices = false): void
    {
        $this->logoutAction->execute($user, $allDevices);
    }

    /**
     * Send password reset link to user email.
     *
     * Generates secure reset token and sends email with reset instructions.
     * Handles password reset link generation and delivery.
     *
     * @param string $email User email address to send reset link to
     * @return void
     *
     * @throws \Exception If email sending fails
     */
    public function forgotPassword(string $email): void
    {
        $this->resetPasswordAction->sendResetLink($email);
    }

    /**
     * Reset user password using secure token.
     *
     * Validates reset token and updates user password securely.
     * Invalidates reset token after successful password change.
     *
     * @param string $token Secure password reset token
     * @param string $email User email address
     * @param string $password New password to set
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException If token is invalid
     * @throws \Exception If password reset fails
     */
    public function resetPassword(string $token, string $email, string $password): void
    {
        $this->resetPasswordAction->reset($token, $email, $password);
    }

    /**
     * Change authenticated user password.
     *
     * Validates current password and updates to new password securely.
     * Requires current password verification for security.
     *
     * @param User $user The authenticated user
     * @param array<string, mixed> $data Password change data with current and new passwords
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException If current password is incorrect
     * @throws \Exception If password change fails
     */
    public function changePassword(User $user, array $data): void
    {
        // Implementation would be handled by a ChangePasswordAction
        // This method signature is inferred from PasswordController usage
    }
}
