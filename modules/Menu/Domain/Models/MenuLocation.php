<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * MenuLocation Model - Defines available menu placement locations.
 *
 * This model represents predefined locations in the theme where
 * menus can be assigned (e.g., header, footer, sidebar).
 *
 * @property string $id UUID primary key
 * @property string $slug Unique location identifier
 * @property string $name Location display name
 * @property string|null $description Location description
 * @property bool $is_active Whether location is active
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MenuLocation active() Filter active locations
 * @method static \Illuminate\Database\Eloquent\Builder|MenuLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuLocation query()
 */
class MenuLocation extends Model
{
    use HasUuids;

    protected $table = 'menu_locations';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Find a location by its slug.
     *
     * @param string $slug The location slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Scope to filter only active locations.
     *
     * @param \Illuminate\Database\Eloquent\Builder<MenuLocation> $query
     * @return \Illuminate\Database\Eloquent\Builder<MenuLocation>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
