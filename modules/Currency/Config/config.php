<?php

return [
    'name' => 'Currency',

    'default' => env('DEFAULT_CURRENCY', 'USD'),

    'precision' => 2,

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'currency_',
    ],

    'exchange_rate' => [
        'provider' => env('EXCHANGE_RATE_PROVIDER', 'exchangerate-api'),
        'api_key' => env('EXCHANGE_RATE_API_KEY'),
        'auto_update' => true,
        'update_frequency' => 'daily',
    ],
];
