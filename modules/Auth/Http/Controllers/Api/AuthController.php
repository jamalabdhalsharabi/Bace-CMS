<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Domain\DTO\LoginData;
use Modules\Auth\Domain\DTO\RegisterData;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Http\Resources\UserResource;

class AuthController extends BaseController
{
    public function __construct(
        protected AuthCommandService $authService
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(new LoginData(
            email: $request->validated('email'),
            password: $request->validated('password')
        ));

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'] ?? 'Bearer',
        ], 'Login successful');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->authService->register(new RegisterData(
            name: $data['name'],
            email: $data['email'],
            password: $data['password']
        ));

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
