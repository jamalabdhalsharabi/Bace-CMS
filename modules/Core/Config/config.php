<?php

return [
    'name' => 'Core',
    
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    'uploads' => [
        'max_size' => 10240, // KB
        'allowed_mimes' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'video' => ['mp4', 'webm', 'ogg'],
            'audio' => ['mp3', 'wav', 'ogg'],
        ],
    ],

    'cache' => [
        'ttl' => 3600,
        'prefix' => 'cms_',
    ],
];
