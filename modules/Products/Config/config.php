<?php

return [
    'name' => 'Products',

    'types' => ['physical', 'digital', 'virtual', 'subscription'],

    'statuses' => ['draft', 'pending', 'published', 'archived'],

    'visibility' => ['visible', 'hidden', 'catalog_only', 'search_only'],

    'stock_statuses' => ['in_stock', 'out_of_stock', 'on_backorder'],

    'inventory' => [
        'track_inventory' => true,
        'allow_backorders' => false,
        'low_stock_threshold' => 10,
    ],

    'per_page' => 20,
];
