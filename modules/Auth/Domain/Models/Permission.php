<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'module',
        'group',
    ];

    /**
     * Get roles with this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Scope: Filter by module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Filter by group.
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get permissions grouped by module.
     */
    public static function getGroupedByModule(): array
    {
        return static::all()
            ->groupBy('module')
            ->map(fn ($perms) => $perms->groupBy('group'))
            ->toArray();
    }

    /**
     * Find permission by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
