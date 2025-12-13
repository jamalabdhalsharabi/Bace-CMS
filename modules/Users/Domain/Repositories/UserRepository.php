<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Users\Domain\Models\User;

/**
 * User Repository.
 *
 * @extends BaseRepository<User>
 */
final class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated users with filters.
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
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * Get active users.
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query()->where('status', 'active')->get();
    }
}
