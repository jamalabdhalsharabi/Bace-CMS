<?php

declare(strict_types=1);

namespace Modules\Users\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Users\Contracts\UserRepositoryContract;
use Modules\Users\Domain\Models\User;

class UserRepository implements UserRepositoryContract
{
    public function __construct(
        protected User $model
    ) {}

    public function all(): Collection
    {
        return $this->model->with('profile')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('profile')->latest()->paginate($perPage);
    }

    public function find(string $id): ?User
    {
        return $this->model->with('profile')->find($id);
    }

    public function findOrFail(string $id): User
    {
        return $this->model->with('profile')->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->with('profile')->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['profile']);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }

    public function restore(User $user): bool
    {
        return $user->restore();
    }

    public function search(string $term): Collection
    {
        return $this->model->with('profile')->search($term)->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->with('profile')->where('status', $status)->get();
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
