<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Users\Domain\Contracts\UserRepositoryInterface;
use Modules\Users\Domain\Models\User;

/**
 * User Repository Implementation.
 *
 * Concrete implementation of UserRepositoryInterface.
 * Handles all data access operations for users including
 * authentication lookups, filtering, and role-based queries.
 *
 * This repository:
 * - Extends BaseRepository for common CRUD operations
 * - Implements UserRepositoryInterface for type safety
 * - Supports searching by email, name (via profile)
 * - Provides filtering by status, role, and verification
 *
 * @extends BaseRepository<User>
 * @implements UserRepositoryInterface
 *
 * @package Modules\Users\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new UserRepository instance.
     *
     * @param User $model The User model instance
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     *
     * Searches across email and profile fields (first_name, last_name).
     * Supports filtering by status, verification, and role.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => 
                $q->where('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('profile', fn ($p) => 
                      $p->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                  )
            );
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['verified'])) {
            $query->whereNotNull('email_verified_at');
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $filters['role']));
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     *
     * Performs case-insensitive email lookup.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     *
     * Returns users with status = 'active'.
     */
    public function getActive(): Collection
    {
        return $this->query()->where('status', 'active')->get();
    }

    /**
     * {@inheritdoc}
     *
     * Queries the user_roles pivot table via roles relationship.
     */
    public function getByRole(string $roleSlug): Collection
    {
        return $this->query()
            ->whereHas('roles', fn ($q) => $q->where('slug', $roleSlug))
            ->get();
    }

    /**
     * {@inheritdoc}
     *
     * Filters by created_at within the specified days.
     */
    public function getRecentlyRegistered(int $days = 7, int $limit = 10): Collection
    {
        return $this->query()
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->limit($limit)
            ->get();
    }
}
