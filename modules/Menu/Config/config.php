<?php

return [
    'name' => 'Menu',

    'locations' => [
        'header' => 'Header Navigation',
        'footer' => 'Footer Navigation',
        'sidebar' => 'Sidebar Navigation',
        'mobile' => 'Mobile Navigation',
    ],

    'item_types' => [
        'page' => 'Page',
        'article' => 'Article',
        'taxonomy' => 'Category/Tag',
        'custom' => 'Custom Link',
        'module' => 'Module Link',
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
