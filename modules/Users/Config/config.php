<?php

return [
    'name' => 'Users',

    'defaults' => [
        'status' => 'active',
        'locale' => 'en',
        'timezone' => 'UTC',
    ],

    'avatars' => [
        'disk' => 'public',
        'path' => 'avatars',
        'max_size' => 2048, // KB
        'dimensions' => [
            'width' => 200,
            'height' => 200,
        ],
    ],

    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'pending' => 'Pending Verification',
    ],
];
