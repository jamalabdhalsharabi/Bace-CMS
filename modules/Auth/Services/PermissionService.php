<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Contracts\PermissionServiceContract;
use Modules\Auth\Domain\Models\Permission;

/**
 * Class PermissionService
 *
 * Service class for managing permissions including
 * CRUD and module permission registration.
 *
 * @package Modules\Auth\Services
 */
class PermissionService implements PermissionServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        return Permission::all();
    }

    /**
     * {@inheritdoc}
     */
    public function getGrouped(): array
    {
        return Permission::getGroupedByModule();
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Permission
    {
        return Permission::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Permission
    {
        return Permission::findBySlug($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Permission
    {
        return Permission::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'module' => $data['module'] ?? 'general',
            'group' => $data['group'] ?? 'general',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function createMany(array $permissions): Collection
    {
        $created = collect();

        foreach ($permissions as $permission) {
            $created->push($this->create($permission));
        }

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Permission $permission): bool
    {
        $permission->roles()->detach();

        return $permission->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function registerModulePermissions(string $module, array $permissions): void
    {
        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $slug => $name) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'module' => $module,
                        'group' => $group,
                    ]
                );
            }
        }
    }
}
