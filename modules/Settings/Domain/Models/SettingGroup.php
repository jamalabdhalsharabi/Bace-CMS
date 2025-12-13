<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SettingGroup Model - Organizes settings into logical groups.
 *
 * This model represents setting categories like 'general', 'email',
 * 'seo', etc. for organizing application settings.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique group identifier
 * @property string $name Group display name
 * @property int $sort_order Display order
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Setting> $settings Settings in this group
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup ordered() Order by sort_order
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup query()
 */
class SettingGroup extends Model
{
    use HasUuids;

    protected $table = 'setting_groups';

    protected $fillable = [
        'slug',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get all settings in this group.
     *
     * @return HasMany<Setting>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'group', 'slug');
    }

    /**
     * Find a group by its slug.
     *
     * @param string $slug The group slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Scope to order by sort_order.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SettingGroup> $query
     * @return \Illuminate\Database\Eloquent\Builder<SettingGroup>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
