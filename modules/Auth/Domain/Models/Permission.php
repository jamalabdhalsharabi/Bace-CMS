<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Model - Defines granular access permissions.
 *
 * This model represents individual permissions in the RBAC system.
 * Permissions are assigned to roles, which are then assigned to users.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique permission identifier (e.g., 'articles.create', 'users.delete')
 * @property string $name Human-readable permission name
 * @property string|null $group_name Group for organizing permissions (e.g., 'Articles', 'Users')
 * @property string|null $description Permission description for documentation
 * @property string $guard_name Authentication guard name (default: 'web')
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles Roles that have this permission
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Permission forGroup(string $group) Filter by group name
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 */
class Permission extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    protected $fillable = [
        'slug',
        'name',
        'group_name',
        'description',
        'guard_name',
    ];

    /**
     * Get all roles that have this permission.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Scope to filter permissions by group name.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Permission> $query
     * @param string $group The group name to filter by
     * @return \Illuminate\Database\Eloquent\Builder<Permission>
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group_name', $group);
    }

    /**
     * Get all permissions grouped by their group_name.
     *
     * Useful for displaying permissions in admin interfaces
     * organized by functional area.
     *
     * @return array<string, array<int, array<string, mixed>>> Permissions grouped by group_name
     */
    public static function getGrouped(): array
    {
        return static::all()->groupBy('group_name')->toArray();
    }

    /**
     * Find a permission by its unique slug.
     *
     * @param string $slug The permission slug to search for
     * @return self|null The permission or null if not found
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
