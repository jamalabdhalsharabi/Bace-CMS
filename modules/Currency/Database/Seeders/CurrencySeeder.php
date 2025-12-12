<?php

declare(strict_types=1);

namespace Modules\Currency\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Currency\Domain\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'is_default' => true,
                'is_active' => true,
                'ordering' => 1,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'symbol_position' => 'after',
                'decimal_separator' => ',',
                'thousand_separator' => '.',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'ordering' => 2,
            ],
            [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'symbol' => 'ر.س',
                'symbol_position' => 'after',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'ordering' => 3,
            ],
        ];

        foreach ($currencies as $curr) {
            Currency::firstOrCreate(['code' => $curr['code']], $curr);
        }
    }
}
