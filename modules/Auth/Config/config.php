<?php

return [
    'name' => 'Auth',

    'super_admin_role' => 'super-admin',

    'default_role' => 'user',

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_numeric' => true,
        'require_special' => false,
    ],

    'tokens' => [
        'access_token_expiry' => 60 * 24, // 24 hours in minutes
        'refresh_token_expiry' => 60 * 24 * 7, // 7 days in minutes
    ],

    'throttle' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
];
