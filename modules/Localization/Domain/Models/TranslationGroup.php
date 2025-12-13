<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TranslationGroup Model - Organizes translation keys into groups.
 *
 * This model represents logical groupings for translation keys
 * such as 'auth', 'validation', 'messages', etc.
 *
 * @property string $id UUID primary key
 * @property string $name Unique group name
 * @property string|null $description Group description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TranslationKey> $keys Translation keys in this group
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationGroup query()
 */
class TranslationGroup extends Model
{
    use HasUuids;

    protected $table = 'translation_groups';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get all translation keys in this group.
     *
     * @return HasMany<TranslationKey>
     */
    public function keys(): HasMany
    {
        return $this->hasMany(TranslationKey::class, 'group_id');
    }

    /**
     * Find a group by its name.
     *
     * @param string $name The group name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }
}
