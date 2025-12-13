<?php

declare(strict_types=1);

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Users\Application\Services\UserCommandService;
use Modules\Users\Application\Services\UserQueryService;
use Modules\Users\Http\Resources\UserResource;

class ProfileController extends BaseController
{
    public function __construct(
        protected UserQueryService $queryService,
        protected UserCommandService $commandService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(new UserResource($user));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $user = $this->commandService->update($user->id, $request->only(['name', 'email', 'locale', 'timezone']));

        return $this->success(new UserResource($user), 'Profile updated successfully');
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();
        // TODO: Implement avatar upload via Media module
        
        return $this->success(new UserResource($user), 'Avatar updated successfully');
    }

    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->commandService->update($user->id, ['avatar_id' => null]);

        return $this->success(null, 'Avatar removed successfully');
    }
}
