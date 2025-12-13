<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\HasOrdering;

/**
 * MenuItem Model - Represents an individual navigation link.
 *
 * This model handles menu items with hierarchy support, polymorphic
 * linking to content, localized titles, and visibility conditions.
 *
 * @property string $id UUID primary key
 * @property string $menu_id Foreign key to menus table
 * @property string|null $parent_id Foreign key to parent menu item
 * @property string $type Item type (custom, page, article, category, etc.)
 * @property string|null $linkable_id UUID of linked entity
 * @property string|null $linkable_type Polymorphic model type
 * @property array $title Localized titles as JSON {locale: title}
 * @property string|null $url Custom URL for 'custom' type
 * @property string $target Link target (_self, _blank)
 * @property string|null $icon Icon class name
 * @property string|null $css_class Additional CSS classes
 * @property int $ordering Sort order
 * @property bool $is_active Whether item is visible
 * @property array|null $conditions Visibility conditions as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Menu $menu Parent menu
 * @property-read MenuItem|null $parent Parent menu item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $children Child items
 * @property-read Model|null $linkable Linked entity (polymorphic)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem active() Filter active items
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem ordered() Order by ordering field
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem query()
 */
class MenuItem extends Model
{
    use HasUuids;
    use HasOrdering;

    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'linkable_id',
        'linkable_type',
        'title',
        'url',
        'target',
        'icon',
        'css_class',
        'ordering',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'title' => 'array',
        'ordering' => 'integer',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Get the parent menu.
     *
     * @return BelongsTo<Menu, MenuItem>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     *
     * @return BelongsTo<MenuItem, MenuItem>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child menu items ordered.
     *
     * @return HasMany<MenuItem>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * Get the linked entity (page, article, etc.).
     *
     * @return MorphTo<Model, MenuItem>
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the localized title for the current locale.
     *
     * @param string|null $value Raw JSON value
     * @return string The localized title
     */
    public function getTitleAttribute($value): string
    {
        $titles = json_decode($value, true) ?? [];
        return $titles[app()->getLocale()] ?? $titles['en'] ?? '';
    }

    /**
     * Get all localized titles.
     *
     * @return array<string, string> Titles keyed by locale
     */
    public function getLocalizedTitles(): array
    {
        return json_decode($this->attributes['title'] ?? '{}', true) ?? [];
    }

    /**
     * Get the resolved URL for this menu item.
     *
     * @param string|null $value Raw URL value
     * @return string The URL or '#' as fallback
     */
    public function getUrlAttribute($value): string
    {
        if ($this->type === 'custom') {
            return $value ?? '#';
        }

        if ($this->linkable) {
            return $this->linkable->url ?? '#';
        }

        return $value ?? '#';
    }

    /**
     * Check if this item has child items.
     *
     * @return bool True if has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Scope to filter only active items.
     *
     * @param \Illuminate\Database\Eloquent\Builder<MenuItem> $query
     * @return \Illuminate\Database\Eloquent\Builder<MenuItem>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by ordering field.
     *
     * @param \Illuminate\Database\Eloquent\Builder<MenuItem> $query
     * @return \Illuminate\Database\Eloquent\Builder<MenuItem>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }
}
