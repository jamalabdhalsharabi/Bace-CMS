<?php

declare(strict_types=1);

namespace Modules\Users\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Modules\Users\Domain\Models\User;
use Modules\Users\Http\DTOs\CreateUserDTO;
use Modules\Users\Http\DTOs\UpdateUserDTO;

interface UserServiceContract
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(CreateUserDTO $dto): User;

    public function update(User $user, UpdateUserDTO $dto): User;

    public function updateProfile(User $user, array $data): User;

    public function updateAvatar(User $user, UploadedFile $file): User;

    public function removeAvatar(User $user): User;

    public function changePassword(User $user, string $password): User;

    public function activate(User $user): User;

    public function suspend(User $user): User;

    public function delete(User $user): bool;
}
