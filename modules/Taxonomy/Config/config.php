<?php

return [
    'name' => 'Taxonomy',

    'default_types' => [
        [
            'slug' => 'category',
            'name' => ['en' => 'Category', 'ar' => 'تصنيف'],
            'is_hierarchical' => true,
            'is_multiple' => true,
            'applies_to' => ['articles', 'products'],
        ],
        [
            'slug' => 'tag',
            'name' => ['en' => 'Tag', 'ar' => 'وسم'],
            'is_hierarchical' => false,
            'is_multiple' => true,
            'applies_to' => ['articles', 'products'],
        ],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
