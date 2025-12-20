<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Password Reset Controller.
 *
 * Handles password reset and forgot password functionality.
 * Follows Single Responsibility Principle by focusing solely on password recovery operations.
 *
 * @package Modules\Auth\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PasswordResetController extends BaseController
{
    /**
     * Create a new PasswordResetController instance.
     *
     * @param AuthCommandService $authService Service for authentication operations
     */
    public function __construct(
        private readonly AuthCommandService $authService
    ) {}

    /**
     * Send password reset link to user email.
     *
     * Validates the email address and sends a password reset link
     * via email to the user for secure password recovery.
     *
     * @param ForgotPasswordRequest $request Validated forgot password request
     * @return JsonResponse Success confirmation message
     *
     * @throws \Exception If email sending fails
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->forgotPassword($request->validated()['email']);
            
            return $this->success(null, 'Password reset link sent');
        } catch (\Throwable $e) {
            return $this->error('Failed to send reset link: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password using token.
     *
     * Validates the reset token and updates the user's password
     * with the new provided password securely.
     *
     * @param ResetPasswordRequest $request Validated reset password request
     * @return JsonResponse Success confirmation message
     *
     * @throws \Illuminate\Validation\ValidationException If token is invalid
     * @throws \Exception If password reset fails
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->authService->resetPassword($data['token'], $data['email'], $data['password']);
            
            return $this->success(null, 'Password reset successfully');
        } catch (\Throwable $e) {
            return $this->error('Password reset failed: ' . $e->getMessage(), 422);
        }
    }
}
