<?php

return [
    'name' => 'Search',

    'driver' => env('SEARCH_DRIVER', 'database'),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', ''),
    ],

    'indices' => [
        'articles' => [
            'model' => \Modules\Content\Domain\Models\Article::class,
            'searchable' => ['title', 'content', 'excerpt'],
            'filterable' => ['type', 'status', 'author_id'],
            'sortable' => ['created_at', 'published_at', 'view_count'],
        ],
        'pages' => [
            'model' => \Modules\Content\Domain\Models\Page::class,
            'searchable' => ['title', 'content'],
            'filterable' => ['status', 'template'],
            'sortable' => ['created_at', 'ordering'],
        ],
        'taxonomies' => [
            'model' => \Modules\Taxonomy\Domain\Models\Taxonomy::class,
            'searchable' => ['name', 'description'],
            'filterable' => ['type_id', 'is_active'],
            'sortable' => ['ordering'],
        ],
    ],

    'per_page' => 20,

    'min_query_length' => 2,

    'highlight' => [
        'enabled' => true,
        'tag' => 'mark',
    ],
];
