<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasTranslations;

/**
 * Menu Model - Represents a navigation menu structure.
 *
 * This model manages navigation menus with hierarchical items,
 * location assignments, and active state management.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique URL-friendly identifier
 * @property string $name Menu display name
 * @property string|null $location Assigned location (e.g., 'header', 'footer')
 * @property bool $is_active Whether menu is active
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $items Root-level menu items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $allItems All menu items
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Menu active() Filter active menus
 * @method static \Illuminate\Database\Eloquent\Builder|Menu location(string $location) Filter by location
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 */
class Menu extends Model
{
    use HasUuids;
    use HasTranslations;

    public array $translatedAttributes = ['name', 'description'];

    protected $table = 'menus';

    protected $fillable = [
        'slug',
        'name',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get root-level menu items ordered.
     *
     * @return HasMany<MenuItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->ordered();
    }

    /**
     * Get all menu items regardless of hierarchy.
     *
     * @return HasMany<MenuItem>
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get the complete menu tree with nested children.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, MenuItem>
     */
    public function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->items()->with('children.children')->get();
    }

    /**
     * Scope to filter only active menus.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Menu> $query
     * @return \Illuminate\Database\Eloquent\Builder<Menu>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter menus by location.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Menu> $query
     * @param string $location The location name
     * @return \Illuminate\Database\Eloquent\Builder<Menu>
     */
    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Find a menu by its slug.
     *
     * @param string $slug The menu slug
     * @return self|null The menu or null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Find the active menu for a location.
     *
     * @param string $location The location name
     * @return self|null The menu or null
     */
    public static function findByLocation(string $location): ?self
    {
        return static::where('location', $location)->active()->first();
    }
}
