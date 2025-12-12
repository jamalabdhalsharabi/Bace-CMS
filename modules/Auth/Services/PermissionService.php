<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Contracts\PermissionServiceContract;
use Modules\Auth\Domain\Models\Permission;

class PermissionService implements PermissionServiceContract
{
    public function all(): Collection
    {
        return Permission::all();
    }

    public function getGrouped(): array
    {
        return Permission::getGroupedByModule();
    }

    public function find(string $id): ?Permission
    {
        return Permission::find($id);
    }

    public function findBySlug(string $slug): ?Permission
    {
        return Permission::findBySlug($slug);
    }

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

    public function createMany(array $permissions): Collection
    {
        $created = collect();

        foreach ($permissions as $permission) {
            $created->push($this->create($permission));
        }

        return $created;
    }

    public function delete(Permission $permission): bool
    {
        $permission->roles()->detach();

        return $permission->delete();
    }

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
