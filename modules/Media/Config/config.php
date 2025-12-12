<?php

return [
    'name' => 'Media',

    'disk' => env('MEDIA_DISK', 'public'),

    'path' => 'media',

    'max_size' => env('MEDIA_MAX_SIZE', 10240), // KB

    'allowed_mimes' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'video' => ['mp4', 'webm', 'ogg', 'mov'],
        'audio' => ['mp3', 'wav', 'ogg', 'aac'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
    ],

    'image' => [
        'driver' => 'gd', // gd or imagick
        'quality' => 85,
        'conversions' => [
            'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'crop'],
            'small' => ['width' => 300, 'height' => 300, 'fit' => 'contain'],
            'medium' => ['width' => 600, 'height' => 600, 'fit' => 'contain'],
            'large' => ['width' => 1200, 'height' => 1200, 'fit' => 'contain'],
        ],
    ],

    'generate_conversions' => true,

    'hash_filenames' => true,
];
