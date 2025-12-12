<?php

return [
    'name' => 'Content',

    'articles' => [
        'types' => ['post', 'news', 'tutorial'],
        'default_type' => 'post',
        'statuses' => ['draft', 'pending', 'published', 'archived'],
        'per_page' => 15,
        'enable_comments' => true,
        'enable_revisions' => true,
    ],

    'pages' => [
        'templates' => ['default', 'full-width', 'sidebar-left', 'sidebar-right'],
        'default_template' => 'default',
        'per_page' => 15,
    ],

    'services' => [
        'per_page' => 12,
    ],

    'seo' => [
        'title_max_length' => 60,
        'description_max_length' => 160,
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
