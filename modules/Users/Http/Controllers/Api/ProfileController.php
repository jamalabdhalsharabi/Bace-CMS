<?php

declare(strict_types=1);

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Application\Services\UserCommandService;
use Modules\Users\Application\Services\UserQueryService;
use Modules\Users\Http\Requests\UpdateAvatarRequest;
use Modules\Users\Http\Requests\UpdateProfileRequest;
use Modules\Users\Http\Resources\UserResource;

/**
 * Profile API Controller.
 *
 * Follows Clean Architecture principles:
 * - No validation logic (delegated to Form Requests)
 * - No business logic (delegated to Services)
 */
class ProfileController extends BaseController
{
    public function __construct(
        protected UserQueryService $queryService,
        protected UserCommandService $commandService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->commandService->update($request->user()->id, $request->validated());
        return $this->success(new UserResource($user), 'Profile updated successfully');
    }

    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $user = $this->commandService->updateAvatar($request->user(), $request->file('avatar'));
        return $this->success(new UserResource($user), 'Avatar updated successfully');
    }

    public function removeAvatar(Request $request): JsonResponse
    {
        $this->commandService->removeAvatar($request->user());
        return $this->success(null, 'Avatar removed successfully');
    }
}
