<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Contracts\RoleServiceContract;
use Modules\Auth\Domain\Models\Role;

class RoleService implements RoleServiceContract
{
    public function all(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function find(string $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function findBySlug(string $slug): ?Role
    {
        return Role::with('permissions')->where('slug', $slug)->first();
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_system' => $data['is_system'] ?? false,
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh(['permissions']);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'] ?? $role->name,
            'description' => $data['description'] ?? $role->description,
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh(['permissions']);
    }

    public function delete(Role $role): bool
    {
        if ($role->is_system) {
            throw new \RuntimeException('Cannot delete system role.');
        }

        $role->permissions()->detach();
        $role->users()->detach();

        return $role->delete();
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->fresh(['permissions']);
    }
}
