<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Domain\DTO\LoginData;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Http\Resources\UserResource;

/**
 * Authentication Controller.
 *
 * Handles core authentication operations: login, logout, and user profile retrieval.
 * Follows Single Responsibility Principle by focusing solely on authentication flow.
 *
 * @package Modules\Auth\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class AuthenticationController extends BaseController
{
    /**
     * Create a new AuthenticationController instance.
     *
     * @param AuthCommandService $authService Service for authentication operations
     */
    public function __construct(
        private readonly AuthCommandService $authService
    ) {}

    /**
     * Authenticate user with email and password.
     *
     * Validates credentials, generates API token, and returns user data
     * with authentication token for subsequent API calls.
     *
     * @param LoginRequest $request Validated login request containing email and password
     * @return JsonResponse User data with authentication token
     *
     * @throws \Illuminate\Validation\ValidationException If credentials are invalid
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(new LoginData(
                email: $request->validated('email'),
                password: $request->validated('password')
            ));

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'] ?? 'Bearer',
            ], 'Login successful');
        } catch (\Throwable $e) {
            return $this->error('Authentication failed: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Log out the authenticated user.
     *
     * Revokes the current authentication token and terminates the session.
     * Optionally can revoke all tokens for all devices.
     *
     * @param Request $request HTTP request with authenticated user
     * @return JsonResponse Success confirmation message
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->success(null, 'Logged out successfully');
        } catch (\Throwable $e) {
            return $this->error('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current authenticated user profile.
     *
     * Returns comprehensive user information including profile data,
     * assigned roles, and all available permissions for the authenticated user.
     *
     * @param Request $request HTTP request with authenticated user
     * @return JsonResponse User profile with roles and permissions
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['profile', 'roles.permissions']);

            return $this->success([
                'user' => new UserResource($user),
                'permissions' => $user->getAllPermissions()->pluck('slug'),
            ]);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve user profile: ' . $e->getMessage());
        }
    }
}
