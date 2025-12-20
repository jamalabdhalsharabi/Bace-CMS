<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Domain\DTO\RegisterData;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Http\Resources\UserResource;

/**
 * Registration Controller.
 *
 * Handles new user registration and account creation processes.
 * Follows Single Responsibility Principle by focusing solely on user registration.
 *
 * @package Modules\Auth\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RegistrationController extends BaseController
{
    /**
     * Create a new RegistrationController instance.
     *
     * @param AuthCommandService $authService Service for authentication operations
     */
    public function __construct(
        private readonly AuthCommandService $authService
    ) {}

    /**
     * Register a new user account.
     *
     * Creates a new user account with profile information and assigns
     * the default role. Validates input data and handles registration flow.
     *
     * @param RegisterRequest $request Validated registration request
     * @return JsonResponse Created user data
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     * @throws \Exception If registration process fails
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $this->authService->register(new RegisterData(
                email: $data['email'],
                password: $data['password'],
                first_name: $data['first_name'] ?? null,
                last_name: $data['last_name'] ?? null,
                phone: $data['phone'] ?? null
            ));

            return $this->created(new UserResource($user), 'Registration successful');
        } catch (\Throwable $e) {
            return $this->error('Registration failed: ' . $e->getMessage(), 422);
        }
    }
}
