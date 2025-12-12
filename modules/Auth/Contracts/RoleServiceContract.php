<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Domain\Models\Role;

interface RoleServiceContract
{
    public function all(): Collection;

    public function find(string $id): ?Role;

    public function findBySlug(string $slug): ?Role;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): bool;

    public function syncPermissions(Role $role, array $permissions): Role;
}
