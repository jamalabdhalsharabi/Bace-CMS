<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Currency
 *
 * Eloquent model representing a currency
 * with formatting and exchange rate support.
 *
 * @package Modules\Currency\Domain\Models
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property string $symbol_position
 * @property string $decimal_separator
 * @property string $thousand_separator
 * @property int $decimal_places
 * @property bool $is_default
 * @property bool $is_active
 * @property int $ordering
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ExchangeRate> $exchangeRates Exchange rates from this currency
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Currency active() Filter active currencies
 * @method static \Illuminate\Database\Eloquent\Builder|Currency ordered() Order by ordering field
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 */
class Currency extends Model
{
    use HasUuids;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'decimal_separator',
        'thousand_separator',
        'decimal_places',
        'is_default',
        'is_active',
        'ordering',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'ordering' => 'integer',
    ];

    /**
     * Get exchange rates from this currency.
     *
     * @return HasMany<ExchangeRate>
     */
    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    /**
     * Format an amount in this currency.
     *
     * @param float $amount The amount to format
     * @return string Formatted currency string
     */
    public function format(float $amount): string
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->symbol_position === 'before'
            ? $this->symbol . $formatted
            : $formatted . $this->symbol;
    }

    /**
     * Scope to filter only active currencies.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Currency> $query
     * @return \Illuminate\Database\Eloquent\Builder<Currency>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order currencies by ordering field.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Currency> $query
     * @return \Illuminate\Database\Eloquent\Builder<Currency>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }

    /**
     * Get the default currency.
     *
     * @return self|null The default currency or null
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Find a currency by its code.
     *
     * @param string $code The currency code (e.g., 'USD')
     * @return self|null The currency or null
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }

    /**
     * Set this currency as the default.
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
