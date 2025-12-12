<?php

return [
    'name' => 'Localization',

    'default_locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'supported_locales' => ['en', 'ar'],

    'detect_from' => [
        'url_segment',
        'query_param',
        'header',
        'session',
        'cookie',
        'user_preference',
    ],

    'url_segment_position' => 1,

    'query_param' => 'lang',

    'cookie_name' => 'locale',

    'session_key' => 'locale',

    'rtl_locales' => ['ar', 'he', 'fa', 'ur'],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'lang_',
    ],
];
