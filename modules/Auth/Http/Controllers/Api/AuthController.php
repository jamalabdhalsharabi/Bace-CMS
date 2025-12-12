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

class AuthController extends BaseController
{
    public function __construct(
        protected AuthServiceContract $authService
    ) {}

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

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return $this->created(new UserResource($user), 'Registration successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'roles.permissions']);

        return $this->success([
            'user' => new UserResource($user),
            'permissions' => $user->getAllPermissions()->pluck('slug'),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $this->authService->forgotPassword($request->email);

        return $this->success(null, 'Password reset link sent');
    }

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
