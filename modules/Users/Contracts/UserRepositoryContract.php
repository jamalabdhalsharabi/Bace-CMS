<?php

declare(strict_types=1);

namespace Modules\Users\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Users\Domain\Models\User;

interface UserRepositoryContract
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): ?User;

    public function findOrFail(string $id): User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function forceDelete(User $user): bool;

    public function restore(User $user): bool;
}
