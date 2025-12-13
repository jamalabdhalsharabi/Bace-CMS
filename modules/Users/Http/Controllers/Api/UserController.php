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

/**
 * Class UserController
 * 
 * API controller for managing users including CRUD,
 * activation, and suspension.
 * 
 * @package Modules\Users\Http\Controllers\Api
 */
class UserController extends BaseController
{
    /**
     * The user service instance for handling user-related business logic.
     *
     * @var UserServiceContract
     */
    protected UserServiceContract $userService;

    /**
     * Create a new UserController instance.
     *
     * @param UserServiceContract $userService The user service contract implementation
     */
    public function __construct(
        UserServiceContract $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * Display a paginated listing of users.
     *
     * Supports filtering by search term, status, and verification status.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of users wrapped in UserResource
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
     * Display the specified user by their UUID.
     *
     * @param string $id The UUID of the user to retrieve
     * @return JsonResponse The user data wrapped in UserResource or 404 error
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
     * Store a newly created user in the database.
     *
     * @param CreateUserRequest $request The validated request containing user data
     * @return JsonResponse The newly created user wrapped in UserResource (HTTP 201)
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request->validated());
        $user = $this->userService->create($dto);

        return $this->created(new UserResource($user), 'User created successfully');
    }

    /**
     * Update the specified user in the database.
     *
     * @param UpdateUserRequest $request The validated request containing updated user data
     * @param string $id The UUID of the user to update
     * @return JsonResponse The updated user wrapped in UserResource or 404 error
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
     * Delete the specified user from the database.
     *
     * @param string $id The UUID of the user to delete
     * @return JsonResponse Success message or 404 error
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
     * Activate a user account, enabling their access.
     *
     * @param string $id The UUID of the user to activate
     * @return JsonResponse The activated user or 404 error
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
     * Suspend a user account, disabling their access.
     *
     * @param string $id The UUID of the user to suspend
     * @return JsonResponse The suspended user or 404 error
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
