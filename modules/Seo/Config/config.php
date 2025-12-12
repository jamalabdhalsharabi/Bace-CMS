<?php

return [
    'name' => 'Seo',
    
    'defaults' => [
        'title_separator' => ' | ',
        'title_suffix' => env('APP_NAME'),
        'meta_description_length' => 160,
        'meta_title_length' => 60,
    ],

    'sitemap' => [
        'enabled' => true,
        'cache_duration' => 3600,
    ],

    'robots' => [
        'default' => 'index, follow',
    ],

    'redirect_codes' => [301, 302, 307, 308],
];
