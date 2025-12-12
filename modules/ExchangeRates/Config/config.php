<?php

return [
    'name' => 'ExchangeRates',

    'providers' => [
        'exchangerate-api' => [
            'url' => 'https://api.exchangerate-api.com/v4/latest/',
            'key' => env('EXCHANGE_RATE_API_KEY'),
        ],
        'openexchangerates' => [
            'url' => 'https://openexchangerates.org/api/latest.json',
            'key' => env('OPEN_EXCHANGE_RATES_KEY'),
        ],
    ],

    'default_provider' => env('EXCHANGE_RATE_PROVIDER', 'exchangerate-api'),

    'cache_duration' => 3600, // 1 hour

    'auto_update' => [
        'enabled' => true,
        'schedule' => 'hourly',
    ],

    'history' => [
        'retention_days' => 365,
    ],
];
