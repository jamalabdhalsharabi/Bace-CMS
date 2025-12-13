<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TranslationValue Model - Stores translated values for keys.
 *
 * This model holds the actual translated text for a translation
 * key in a specific language.
 *
 * @property string $id UUID primary key
 * @property string $key_id Foreign key to translation_keys
 * @property string $language_id Foreign key to languages
 * @property string|null $value The translated text
 * @property string $status Translation status (draft, published, needs_review)
 * @property bool $is_machine_translated Whether translated by machine
 * @property string|null $translated_by UUID of translator
 * @property string|null $reviewed_by UUID of reviewer
 * @property \Carbon\Carbon|null $reviewed_at When translation was reviewed
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read TranslationKey $key The translation key
 * @property-read Language $language The target language
 * @property-read \App\Models\User|null $translator The translator
 * @property-read \App\Models\User|null $reviewer The reviewer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationValue published() Filter published translations
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationValue query()
 */
class TranslationValue extends Model
{
    use HasUuids;

    protected $table = 'translation_values';

    protected $fillable = [
        'key_id',
        'language_id',
        'value',
        'status',
        'is_machine_translated',
        'translated_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_machine_translated' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the translation key.
     *
     * @return BelongsTo<TranslationKey, TranslationValue>
     */
    public function key(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'key_id');
    }

    /**
     * Get the target language.
     *
     * @return BelongsTo<Language, TranslationValue>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the translator.
     *
     * @return BelongsTo<\App\Models\User, TranslationValue>
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'translated_by');
    }

    /**
     * Get the reviewer.
     *
     * @return BelongsTo<\App\Models\User, TranslationValue>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'reviewed_by');
    }

    /**
     * Scope to filter published translations.
     *
     * @param \Illuminate\Database\Eloquent\Builder<TranslationValue> $query
     * @return \Illuminate\Database\Eloquent\Builder<TranslationValue>
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
