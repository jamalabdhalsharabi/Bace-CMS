<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TranslationKey Model - Represents a translatable string key.
 *
 * This model stores translation keys that can have values
 * in multiple languages.
 *
 * @property string $id UUID primary key
 * @property string|null $group_id Foreign key to translation_groups
 * @property string $key The translation key identifier
 * @property string $type Value type (text, html, pluralized)
 * @property string|null $source Source file or module
 * @property bool $is_system Whether this is a system translation
 * @property bool $is_deprecated Whether this key is deprecated
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read TranslationGroup|null $group Parent group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TranslationValue> $values Translations in all languages
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationKey system() Filter system translations
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationKey query()
 */
class TranslationKey extends Model
{
    use HasUuids;

    protected $table = 'translation_keys';

    protected $fillable = [
        'group_id',
        'key',
        'type',
        'source',
        'is_system',
        'is_deprecated',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_deprecated' => 'boolean',
    ];

    /**
     * Get the parent group.
     *
     * @return BelongsTo<TranslationGroup, TranslationKey>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TranslationGroup::class, 'group_id');
    }

    /**
     * Get all translation values.
     *
     * @return HasMany<TranslationValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class, 'key_id');
    }

    /**
     * Get translation value for a specific language.
     *
     * @param string $languageId Language UUID
     * @return TranslationValue|null
     */
    public function getValueForLanguage(string $languageId): ?TranslationValue
    {
        return $this->values()->where('language_id', $languageId)->first();
    }

    /**
     * Scope to filter system translations.
     *
     * @param \Illuminate\Database\Eloquent\Builder<TranslationKey> $query
     * @return \Illuminate\Database\Eloquent\Builder<TranslationKey>
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
}
