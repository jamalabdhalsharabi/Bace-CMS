<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\AuthServiceContract;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Http\Resources\UserResource;

/**
 * Class AuthController
 * 
 * API controller for authentication including login, register,
 * logout, password reset, and user profile.
 * 
 * @package Modules\Auth\Http\Controllers\Api
 */
class AuthController extends BaseController
{
    /**
     * The authentication service instance.
     *
     * @var AuthServiceContract
     */
    protected AuthServiceContract $authService;

    /**
     * Create a new AuthController instance.
     *
     * @param AuthServiceContract $authService The auth service contract implementation
     */
    public function __construct(
        AuthServiceContract $authService
    ) {
        $this->authService = $authService;
    }

    /**
     * Authenticate user and return access token.
     *
     * @param LoginRequest $request The validated login request with email and password
     * @return JsonResponse User data with access token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password')
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Login successful');
    }

    /**
     * Register a new user account.
     *
     * @param RegisterRequest $request The validated registration request
     * @return JsonResponse The created user (HTTP 201)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return $this->created(new UserResource($user), 'Registration successful');
    }

    /**
     * Logout the authenticated user.
     *
     * Revokes the current access token.
     *
     * @param Request $request The current request with authenticated user
     * @return JsonResponse Success message
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get the authenticated user's profile with permissions.
     *
     * @param Request $request The current request with authenticated user
     * @return JsonResponse User data with permissions
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'roles.permissions']);

        return $this->success([
            'user' => new UserResource($user),
            'permissions' => $user->getAllPermissions()->pluck('slug'),
        ]);
    }

    /**
     * Send a password reset link to the user's email.
     *
     * @param Request $request The request containing the user's email
     * @return JsonResponse Success message
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $this->authService->forgotPassword($request->email);

        return $this->success(null, 'Password reset link sent');
    }

    /**
     * Reset the user's password using the reset token.
     *
     * @param Request $request The request containing token, email, and new password
     * @return JsonResponse Success message
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $this->authService->resetPassword(
            $request->token,
            $request->email,
            $request->password
        );

        return $this->success(null, 'Password reset successfully');
    }
}
