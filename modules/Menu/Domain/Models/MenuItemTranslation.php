<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MenuItemTranslation Model - Stores localized menu item content.
 *
 * This model holds translated labels, titles, and descriptions
 * for menu items in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $menu_item_id Foreign key to menu_items table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $label Translated menu item label
 * @property string|null $title Translated title attribute
 * @property string|null $description Translated description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read MenuItem $menuItem The parent menu item
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItemTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItemTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItemTranslation query()
 */
class MenuItemTranslation extends Model
{
    use HasUuids;

    protected $table = 'menu_item_translations';

    protected $fillable = [
        'menu_item_id',
        'locale',
        'label',
        'title',
        'description',
    ];

    /**
     * Get the menu item that owns this translation.
     *
     * @return BelongsTo<MenuItem, MenuItemTranslation>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
