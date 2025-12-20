<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Domain\DTO\LoginData;
use Modules\Auth\Domain\Events\UserLoggedIn;
use Modules\Core\Application\Actions\Action;
use Modules\Users\Domain\Models\User;

/**
 * Login Action.
 *
 * Handles user authentication, credential validation, and API token generation.
 * Implements secure login flow with activity logging and event dispatching.
 * Validates user status and records login attempts for security monitoring.
 *
 * @package Modules\Auth\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class LoginAction extends Action
{
    /**
     * Execute the login action.
     *
     * @param LoginData $data Login credentials
     * @return array User data and token
     * @throws ValidationException
     */
    public function execute(LoginData $data): array
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $user->recordLogin();
        $token = $user->createToken('auth-token');

        event(new UserLoggedIn($user, request()->ip(), request()->userAgent()));

        return [
            'user' => $user->load('profile'),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }
}
