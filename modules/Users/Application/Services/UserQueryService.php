<?php

declare(strict_types=1);

namespace Modules\Users\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository;

/**
 * User Query Service.
 *
 * Handles all read operations for users via Repository pattern.
 * No write operations - delegates to UserCommandService for mutations.
 *
 * @package Modules\Users\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UserQueryService
{
    /**
     * Create a new UserQueryService instance.
     *
     * @param UserRepository $repository The user repository
     */
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    /**
     * Get paginated list of users.
     *
     * @param array<string, mixed> $filters Available filters: search, status, verified, role
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<User>
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Find a user by ID.
     *
     * @param string $id The user UUID
     *
     * @return User|null
     */
    public function find(string $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * Find a user by email.
     *
     * @param string $email The user email
     *
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Get active users.
     *
     * @return Collection<int, User>
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get users by role.
     *
     * @param string $roleSlug The role slug
     *
     * @return Collection<int, User>
     */
    public function getByRole(string $roleSlug): Collection
    {
        return $this->repository->getByRole($roleSlug);
    }

    /**
     * Get recently registered users.
     *
     * @param int $days Number of days back
     * @param int $limit Maximum number of users
     *
     * @return Collection<int, User>
     */
    public function getRecentlyRegistered(int $days = 7, int $limit = 10): Collection
    {
        return $this->repository->getRecentlyRegistered($days, $limit);
    }
}
