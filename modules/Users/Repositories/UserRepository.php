<?php

declare(strict_types=1);

namespace Modules\Users\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Users\Contracts\UserRepositoryContract;
use Modules\Users\Domain\Models\User;

/**
 * Class UserRepository
 *
 * Repository class for user data access operations
 * implementing the UserRepositoryContract.
 *
 * @package Modules\Users\Repositories
 */
class UserRepository implements UserRepositoryContract
{
    /**
     * Create a new UserRepository instance.
     *
     * @param User $model The User model instance
     */
    public function __construct(
        protected User $model
    ) {}

    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        return $this->model->with('profile')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('profile')->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?User
    {
        return $this->model->with('profile')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(string $id): User
    {
        return $this->model->with('profile')->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->with('profile')->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['profile']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function restore(User $user): bool
    {
        return $user->restore();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $term): Collection
    {
        return $this->model->with('profile')->search($term)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->with('profile')->where('status', $status)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
