<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Auth\Http\Requests\ChangePasswordRequest;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Password API Controller.
 *
 * Follows Clean Architecture principles.
 */
class PasswordController extends BaseController
{
    public function __construct(
        protected AuthCommandService $authService
    ) {
    }

    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());
        return $this->success(null, 'Password changed successfully');
    }
}
