<?php

return [
    'name' => 'Settings',

    'groups' => [
        'general' => 'General Settings',
        'site' => 'Site Settings',
        'mail' => 'Email Settings',
        'social' => 'Social Media',
        'seo' => 'SEO Settings',
        'api' => 'API Settings',
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'key' => 'app_settings',
    ],

    'defaults' => [
        'site_name' => 'My CMS',
        'site_description' => '',
        'site_logo' => null,
        'site_favicon' => null,
        'timezone' => 'UTC',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'maintenance_mode' => false,
    ],
];
