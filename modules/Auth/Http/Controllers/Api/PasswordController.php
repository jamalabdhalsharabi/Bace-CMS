<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Http\Requests\ChangePasswordRequest;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Password Management Controller.
 *
 * Handles password change operations for authenticated users.
 * Follows Single Responsibility Principle and Clean Architecture principles.
 *
 * @package Modules\Auth\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PasswordController extends BaseController
{
    /**
     * Create a new PasswordController instance.
     *
     * @param AuthCommandService $authService Service for authentication operations
     */
    public function __construct(
        private readonly AuthCommandService $authService
    ) {}

    /**
     * Change authenticated user's password.
     *
     * Validates the current password and updates it with the new password.
     * Requires the user to provide their current password for security.
     *
     * @param ChangePasswordRequest $request Validated password change request
     * @return JsonResponse Success confirmation message
     *
     * @throws \Illuminate\Validation\ValidationException If current password is incorrect
     * @throws \Exception If password change fails
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword($request->user(), $request->validated());
            
            return $this->success(null, 'Password changed successfully');
        } catch (\Throwable $e) {
            return $this->error('Password change failed: ' . $e->getMessage(), 422);
        }
    }
}
