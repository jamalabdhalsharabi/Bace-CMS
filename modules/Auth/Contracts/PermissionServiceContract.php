<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Domain\Models\Permission;

interface PermissionServiceContract
{
    public function all(): Collection;

    public function getGrouped(): array;

    public function find(string $id): ?Permission;

    public function findBySlug(string $slug): ?Permission;

    public function create(array $data): Permission;

    public function createMany(array $permissions): Collection;

    public function delete(Permission $permission): bool;
}
