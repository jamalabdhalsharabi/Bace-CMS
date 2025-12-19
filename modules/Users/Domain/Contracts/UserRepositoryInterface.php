<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Users\Domain\Models\User;

/**
 * User Repository Interface.
 *
 * Defines the contract for user-specific data access operations.
 * Extends the base RepositoryInterface with user management methods
 * including authentication, filtering, and status management.
 *
 * This interface provides methods for:
 * - Finding users by email (for authentication)
 * - Filtering users by status, role, and verification
 * - Managing user search and pagination
 *
 * @extends RepositoryInterface<User>
 *
 * @package Modules\Users\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see \Modules\Users\Domain\Repositories\UserRepository Default implementation
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated users with optional filters.
     *
     * Retrieves a paginated list of users that can be filtered
     * by various criteria such as status, role, and verification.
     *
     * Supported filters:
     * - `search`: Search term for email, first_name, or last_name
     * - `status`: User status (active, inactive, suspended)
     * - `verified`: Boolean, filter email-verified users only
     * - `role`: Role slug to filter by
     *
     * @param array<string, mixed> $filters Associative array of filter criteria
     * @param int                  $perPage Number of items per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated users with metadata
     *
     * @example
     * ```php
     * // Get all active users
     * $users = $repository->getPaginated(['status' => 'active']);
     *
     * // Search users with role filter
     * $users = $repository->getPaginated([
     *     'search' => 'john',
     *     'role' => 'admin',
     *     'verified' => true,
     * ], 20);
     * ```
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a user by email address.
     *
     * Searches for a user by their unique email address.
     * Commonly used for authentication and password reset.
     *
     * @param string $email The user's email address
     *
     * @return User|null The user if found, null otherwise
     *
     * @example
     * ```php
     * $user = $repository->findByEmail('user@example.com');
     * if ($user && Hash::check($password, $user->password)) {
     *     // Authenticate user
     * }
     * ```
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get all active users.
     *
     * Retrieves all users with 'active' status.
     * Useful for notifications, bulk operations, or reporting.
     *
     * @return Collection<int, User> Collection of active users
     *
     * @example
     * ```php
     * $activeUsers = $repository->getActive();
     * foreach ($activeUsers as $user) {
     *     // Send notification
     * }
     * ```
     */
    public function getActive(): Collection;

    /**
     * Get users by role.
     *
     * Retrieves all users assigned to a specific role.
     *
     * @param string $roleSlug The role's unique slug identifier
     *
     * @return Collection<int, User> Collection of users with the specified role
     *
     * @example
     * ```php
     * $admins = $repository->getByRole('admin');
     * $editors = $repository->getByRole('editor');
     * ```
     */
    public function getByRole(string $roleSlug): Collection;

    /**
     * Get recently registered users.
     *
     * Retrieves users who registered within a specified number of days.
     *
     * @param int $days Number of days to look back (default: 7)
     * @param int $limit Maximum number of users to retrieve (default: 10)
     *
     * @return Collection<int, User> Collection of recently registered users
     *
     * @example
     * ```php
     * // Get users registered in the last week
     * $newUsers = $repository->getRecentlyRegistered();
     *
     * // Get users registered in the last 30 days
     * $newUsers = $repository->getRecentlyRegistered(30, 50);
     * ```
     */
    public function getRecentlyRegistered(int $days = 7, int $limit = 10): Collection;
}
