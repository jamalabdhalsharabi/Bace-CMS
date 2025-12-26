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
 * @property string $code ISO 4217 currency code (e.g., 'USD', 'EUR')
 * @property string|null $numeric_code ISO 4217 numeric code
 * @property string $symbol Currency symbol (e.g., '$', '€')
 * @property string|null $symbol_native Native currency symbol
 * @property string $symbol_position Symbol position ('before' or 'after')
 * @property string $decimal_separator Decimal separator character
 * @property string $thousands_separator Thousands separator character
 * @property int $decimal_places Number of decimal places
 * @property string $rounding_mode Rounding mode (half_up, half_down, etc.)
 * @property float|null $rounding_increment Rounding increment value
 * @property bool $is_default Whether this is the default currency
 * @property bool $is_active Whether currency is active
 * @property int $sort_order Display order
 * @property string $created_by UUID of user who created the currency
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
        'numeric_code',
        'name',
        'symbol',
        'symbol_native',
        'symbol_position',
        'decimal_separator',
        'thousands_separator',
        'decimal_places',
        'rounding_mode',
        'rounding_increment',
        'is_default',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'rounding_increment' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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
            $this->thousands_separator
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
     * Scope to order currencies by sort_order field.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Currency> $query
     * @return \Illuminate\Database\Eloquent\Builder<Currency>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
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
