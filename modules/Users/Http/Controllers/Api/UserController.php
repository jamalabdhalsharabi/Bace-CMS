<?php

declare(strict_types=1);

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Application\Services\UserCommandService;
use Modules\Users\Application\Services\UserQueryService;
use Modules\Users\Http\Requests\CreateUserRequest;
use Modules\Users\Http\Requests\UpdateUserRequest;
use Modules\Users\Http\Resources\UserResource;

/**
 * User API Controller.
 *
 * Follows Clean Architecture principles:
 * - No validation logic (delegated to Form Requests)
 * - No business logic (delegated to Services)
 */
class UserController extends BaseController
{
    public function __construct(
        protected UserQueryService $queryService,
        protected UserCommandService $commandService
    ) {
    }

    /**
     * Display a paginated listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->queryService->list(
            filters: $request->only(['search', 'status', 'verified']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(UserResource::collection($users)->resource);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->queryService->find($id);
        return $user ? $this->success(new UserResource($user)) : $this->notFound('User not found');
    }

    /**
     * Store a newly created user.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->commandService->create($request->validated());
        return $this->created(new UserResource($user), 'User created successfully');
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->queryService->find($id);
        if (!$user) return $this->notFound('User not found');

        $updated = $this->commandService->update($id, $request->validated());
        return $this->success(new UserResource($updated), 'User updated successfully');
    }

    /**
     * Delete the specified user.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = $this->queryService->find($id);
        if (!$user) return $this->notFound('User not found');

        $this->commandService->delete($id);
        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Activate a user account.
     */
    public function activate(string $id): JsonResponse
    {
        $user = $this->queryService->find($id);
        if (!$user) return $this->notFound('User not found');

        $activated = $this->commandService->activate($id);
        return $this->success(new UserResource($activated), 'User activated successfully');
    }

    /**
     * Suspend a user account.
     */
    public function suspend(string $id): JsonResponse
    {
        $user = $this->queryService->find($id);
        if (!$user) return $this->notFound('User not found');

        $suspended = $this->commandService->suspend($id);
        return $this->success(new UserResource($suspended), 'User suspended successfully');
    }
}
