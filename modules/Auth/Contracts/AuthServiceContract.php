<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Modules\Users\Domain\Models\User;

interface AuthServiceContract
{
    public function login(string $email, string $password): array;

    public function register(array $data): User;

    public function logout(User $user): void;

    public function refreshToken(string $refreshToken): array;

    public function forgotPassword(string $email): void;

    public function resetPassword(string $token, string $email, string $password): void;

    public function verifyEmail(string $id, string $hash): void;

    public function resendVerification(User $user): void;
}
