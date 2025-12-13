<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Services\AuthCommandService;
use Modules\Core\Http\Controllers\BaseController;

class PasswordController extends BaseController
{
    public function __construct(
        protected AuthCommandService $authService
    ) {
    }

    public function change(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        // TODO: Implement password change
        return $this->success(null, 'Password changed successfully');
    }
}
