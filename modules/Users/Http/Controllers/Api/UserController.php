<?php

declare(strict_types=1);

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Contracts\UserServiceContract;
use Modules\Users\Http\DTOs\CreateUserDTO;
use Modules\Users\Http\DTOs\UpdateUserDTO;
use Modules\Users\Http\Requests\CreateUserRequest;
use Modules\Users\Http\Requests\UpdateUserRequest;
use Modules\Users\Http\Resources\UserResource;

class UserController extends BaseController
{
    public function __construct(
        protected UserServiceContract $userService
    ) {}

    /**
     * List users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->list(
            filters: $request->only(['search', 'status', 'verified']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(
            UserResource::collection($users)->resource
        );
    }

    /**
     * Get single user.
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        return $this->success(new UserResource($user));
    }

    /**
     * Create user.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request->validated());
        $user = $this->userService->create($dto);

        return $this->created(new UserResource($user), 'User created successfully');
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        $dto = UpdateUserDTO::fromRequest($request->validated());
        $user = $this->userService->update($user, $dto);

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    /**
     * Delete user.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        $this->userService->delete($user);

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Activate user.
     */
    public function activate(string $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        $user = $this->userService->activate($user);

        return $this->success(new UserResource($user), 'User activated successfully');
    }

    /**
     * Suspend user.
     */
    public function suspend(string $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        $user = $this->userService->suspend($user);

        return $this->success(new UserResource($user), 'User suspended successfully');
    }
}
