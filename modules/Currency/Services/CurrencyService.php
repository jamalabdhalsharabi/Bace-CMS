<?php

declare(strict_types=1);

namespace Modules\Currency\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Currency\Contracts\CurrencyServiceContract;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Models\ExchangeRate;

class CurrencyService implements CurrencyServiceContract
{
    public function all(): Collection
    {
        return $this->cached('all', fn () => Currency::ordered()->get());
    }

    public function getActive(): Collection
    {
        return $this->cached('active', fn () => Currency::active()->ordered()->get());
    }

    public function find(string $id): ?Currency
    {
        return Currency::find($id);
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->cached("code.{$code}", fn () => Currency::findByCode($code));
    }

    public function getDefault(): ?Currency
    {
        return $this->cached('default', fn () => Currency::getDefault());
    }

    public function create(array $data): Currency
    {
        $currency = Currency::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'symbol_position' => $data['symbol_position'] ?? 'before',
            'decimal_separator' => $data['decimal_separator'] ?? '.',
            'thousand_separator' => $data['thousand_separator'] ?? ',',
            'decimal_places' => $data['decimal_places'] ?? 2,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'ordering' => $data['ordering'] ?? Currency::max('ordering') + 1,
        ]);

        if ($currency->is_default) {
            $currency->setAsDefault();
        }

        $this->clearCache();

        return $currency;
    }

    public function update(Currency $currency, array $data): Currency
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $currency->update($data);

        if (isset($data['is_default']) && $data['is_default']) {
            $currency->setAsDefault();
        }

        $this->clearCache();

        return $currency->fresh();
    }

    public function delete(Currency $currency): bool
    {
        if ($currency->is_default) {
            throw new \RuntimeException('Cannot delete default currency.');
        }

        $result = $currency->delete();
        $this->clearCache();

        return $result;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = ExchangeRate::getRate($from, $to);

        if (!$rate) {
            throw new \RuntimeException("Exchange rate not found for {$from} to {$to}");
        }

        return $rate->convert($amount);
    }

    public function format(float $amount, ?string $currencyCode = null): string
    {
        $currency = $currencyCode
            ? $this->findByCode($currencyCode)
            : $this->getDefault();

        if (!$currency) {
            return number_format($amount, 2);
        }

        return $currency->format($amount);
    }

    public function updateExchangeRate(string $from, string $to, float $rate, string $source = 'manual'): ExchangeRate
    {
        $fromCurrency = Currency::findByCode($from);
        $toCurrency = Currency::findByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \RuntimeException('Currency not found');
        }

        return ExchangeRate::create([
            'from_currency_id' => $fromCurrency->id,
            'to_currency_id' => $toCurrency->id,
            'rate' => $rate,
            'source' => $source,
            'fetched_at' => now(),
        ]);
    }

    protected function cached(string $key, callable $callback): mixed
    {
        if (!config('currency.cache.enabled', true)) {
            return $callback();
        }

        $prefix = config('currency.cache.prefix', 'currency_');
        $ttl = config('currency.cache.ttl', 3600);

        return Cache::remember($prefix . $key, $ttl, $callback);
    }

    public function clearCache(): void
    {
        $prefix = config('currency.cache.prefix', 'currency_');
        Cache::forget($prefix . 'all');
        Cache::forget($prefix . 'active');
        Cache::forget($prefix . 'default');
    }
}
