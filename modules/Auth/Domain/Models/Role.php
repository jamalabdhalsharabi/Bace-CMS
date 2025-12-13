<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model - Defines user roles for authorization.
 *
 * This model represents roles in the RBAC (Role-Based Access Control) system.
 * Roles group permissions together and can be assigned to users.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique URL-friendly identifier (e.g., 'admin', 'editor')
 * @property string $name Human-readable role name
 * @property string|null $description Role description for documentation
 * @property bool $is_system Whether this is a protected system role
 * @property bool $is_default Whether this role is assigned to new users by default
 * @property string $guard_name Authentication guard name (default: 'web')
 * @property string|null $created_by UUID of user who created this role
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions Assigned permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users with this role
 * @property-read \App\Models\User|null $creator User who created this role
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Role nonSystem() Exclude system roles
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 */
class Role extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_system',
        'is_default',
        'guard_name',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the user who created this role.
     *
     * @return BelongsTo<\App\Models\User, Role>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get all permissions assigned to this role.
     *
     * @return BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Get all users who have this role.
     *
     * @return BelongsToMany<\App\Models\User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            'user_roles',
            'role_id',
            'user_id'
        );
    }

    /**
     * Check if this role has a specific permission.
     *
     * @param string $permission Permission slug to check
     * @return bool True if role has the permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Assign a permission to this role.
     *
     * @param Permission|string $permission Permission instance or slug
     * @return self Returns self for method chaining
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If permission slug not found
     */
    public function givePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);

        return $this;
    }

    /**
     * Remove a permission from this role.
     *
     * @param Permission|string $permission Permission instance or slug
     * @return self Returns self for method chaining
     */
    public function revokePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission);
        }

        return $this;
    }

    /**
     * Replace all permissions with the given set.
     *
     * @param array<string> $permissions Array of permission slugs
     * @return self Returns self for method chaining
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Check if this is the super admin role.
     *
     * @return bool True if this is the super admin role
     */
    public function isSuperAdmin(): bool
    {
        return $this->slug === config('auth.super_admin_role', 'super-admin');
    }

    /**
     * Scope to exclude system-protected roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Role> $query
     * @return \Illuminate\Database\Eloquent\Builder<Role>
     */
    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Find a role by its unique slug.
     *
     * @param string $slug The role slug to search for
     * @return self|null The role or null if not found
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
