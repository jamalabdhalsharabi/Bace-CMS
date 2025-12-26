<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Users\Domain\Models\User;

/**
 * Auth Query Service.
 *
 * Handles authentication-related queries.
 */
final class AuthQueryService
{
    /**
     * Get current authenticated user.
     */
    public function currentUser(): ?User
    {
        return request()->user();
    }

    /**
     * Check if user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return request()->user() !== null;
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Check if user has role.
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Get user permissions.
     */
    public function getUserPermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('slug')->toArray();
    }
}
