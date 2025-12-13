<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Language Model - Represents supported application languages.
 *
 * This model manages available languages for the application
 * with support for RTL/LTR text direction and default language.
 *
 * @property string $id UUID primary key
 * @property string $code ISO language code (e.g., 'en', 'ar')
 * @property string $name English language name
 * @property string $native_name Language name in native script
 * @property string $direction Text direction ('ltr' or 'rtl')
 * @property string|null $flag Flag icon or emoji
 * @property bool $is_default Whether this is the default language
 * @property bool $is_active Whether language is enabled
 * @property int $ordering Display order
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Language active() Filter active languages
 * @method static \Illuminate\Database\Eloquent\Builder|Language ordered() Order by ordering field
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 */
class Language extends Model
{
    use HasUuids;

    protected $table = 'languages';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'flag',
        'is_default',
        'is_active',
        'ordering',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'ordering' => 'integer',
    ];

    /**
     * Check if this is a right-to-left language.
     *
     * @return bool True if RTL
     */
    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Check if this is a left-to-right language.
     *
     * @return bool True if LTR
     */
    public function isLtr(): bool
    {
        return $this->direction === 'ltr';
    }

    /**
     * Scope to filter only active languages.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Language> $query
     * @return \Illuminate\Database\Eloquent\Builder<Language>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by ordering field.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Language> $query
     * @return \Illuminate\Database\Eloquent\Builder<Language>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }

    /**
     * Get the default language.
     *
     * @return self|null The default language or null
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Find a language by its code.
     *
     * @param string $code The language code
     * @return self|null The language or null
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get all active language codes.
     *
     * @return array<int, string> Array of language codes
     */
    public static function getActiveCodes(): array
    {
        return static::active()->pluck('code')->toArray();
    }

    /**
     * Set this language as the default.
     *
     * @return self Returns self for method chaining
     */
    public function setAsDefault(): self
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);

        return $this;
    }
}
