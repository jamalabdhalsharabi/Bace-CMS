<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }

    public function setAsDefault(): self
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);

        return $this;
    }
}
