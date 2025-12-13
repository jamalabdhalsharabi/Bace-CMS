<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Contracts\RoleServiceContract;
use Modules\Auth\Domain\Models\Role;

/**
 * Class RoleService
 *
 * Service class for managing roles including
 * CRUD and permission synchronization.
 *
 * @package Modules\Auth\Services
 */
class RoleService implements RoleServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Role
    {
        return Role::with('permissions')->where('slug', $slug)->first();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function delete(Role $role): bool
    {
        if ($role->is_system) {
            throw new \RuntimeException('Cannot delete system role.');
        }

        $role->permissions()->detach();
        $role->users()->detach();

        return $role->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->fresh(['permissions']);
    }
}
