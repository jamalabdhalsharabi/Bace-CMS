<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MenuTranslation Model - Stores localized menu content.
 *
 * This model holds translated names and descriptions for menus
 * in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $menu_id Foreign key to menus table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated menu name
 * @property string|null $description Translated menu description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Menu $menu The parent menu
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MenuTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuTranslation query()
 */
class MenuTranslation extends Model
{
    use HasUuids;

    protected $table = 'menu_translations';

    protected $fillable = [
        'menu_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * Get the menu that owns this translation.
     *
     * @return BelongsTo<Menu, MenuTranslation>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
