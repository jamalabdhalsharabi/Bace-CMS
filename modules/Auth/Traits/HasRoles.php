<?php

declare(strict_types=1);

namespace Modules\Auth\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\Domain\Models\Permission;
use Modules\Auth\Domain\Models\Role;

trait HasRoles
{
    /**
     * Get user roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if user has role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('slug', $roles);
        }

        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    /**
     * Check if user has any role.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if user has all roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);

        return $this;
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role);
        }

        return $this;
    }

    /**
     * Sync roles.
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = Role::whereIn('slug', $roles)->pluck('id');
        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles->some(fn ($role) => $role->hasPermission($permission));
    }

    /**
     * Check if user has any permission.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all user permissions.
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles->flatMap(fn ($role) => $role->permissions)->unique('id');
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('auth.super_admin_role', 'super-admin'));
    }

    /**
     * Scope: Users with role.
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('roles', fn ($q) => $q->where('slug', $role));
    }

    /**
     * Scope: Users with permission.
     */
    public function scopeWithPermission($query, string $permission)
    {
        return $query->whereHas('roles.permissions', fn ($q) => $q->where('slug', $permission));
    }
}
