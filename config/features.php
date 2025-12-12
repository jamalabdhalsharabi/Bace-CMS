<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Define feature flags for your application. Features can be enabled or
    | disabled based on environment, dependencies, or custom logic.
    |
    | Simple format:
    |   'feature_name' => true,
    |
    | Advanced format:
    |   'feature_name' => [
    |       'enabled' => true,
    |       'environments' => ['local', 'staging'],
    |       'depends_on' => ['other_feature'],
    |   ],
    |
    */

    // Core Features
    'multi_language' => [
        'enabled' => env('FEATURE_MULTI_LANGUAGE', true),
        'description' => 'Enable multi-language support',
    ],

    'multi_currency' => [
        'enabled' => env('FEATURE_MULTI_CURRENCY', true),
        'description' => 'Enable multi-currency support',
    ],

    'media_library' => [
        'enabled' => env('FEATURE_MEDIA_LIBRARY', true),
        'description' => 'Enable media library with image processing',
    ],

    // Content Features
    'articles' => [
        'enabled' => env('FEATURE_ARTICLES', true),
        'description' => 'Enable articles/blog functionality',
    ],

    'pages' => [
        'enabled' => env('FEATURE_PAGES', true),
        'description' => 'Enable static pages',
    ],

    'services' => [
        'enabled' => env('FEATURE_SERVICES', true),
        'description' => 'Enable services section',
    ],

    'portfolio' => [
        'enabled' => env('FEATURE_PORTFOLIO', false),
        'description' => 'Enable portfolio/projects section',
    ],

    'products' => [
        'enabled' => env('FEATURE_PRODUCTS', false),
        'description' => 'Enable products/e-commerce functionality',
    ],

    'events' => [
        'enabled' => env('FEATURE_EVENTS', false),
        'description' => 'Enable events management',
    ],

    // Interactive Features
    'comments' => [
        'enabled' => env('FEATURE_COMMENTS', true),
        'description' => 'Enable comments system',
        'depends_on' => ['articles'],
    ],

    'forms' => [
        'enabled' => env('FEATURE_FORMS', true),
        'description' => 'Enable dynamic forms',
    ],

    'newsletter' => [
        'enabled' => env('FEATURE_NEWSLETTER', true),
        'description' => 'Enable newsletter subscription',
    ],

    // Advanced Features
    'search' => [
        'enabled' => env('FEATURE_SEARCH', true),
        'description' => 'Enable full-text search',
    ],

    'analytics' => [
        'enabled' => env('FEATURE_ANALYTICS', true),
        'description' => 'Enable analytics tracking',
    ],

    'seo' => [
        'enabled' => env('FEATURE_SEO', true),
        'description' => 'Enable SEO features',
    ],

    'revisions' => [
        'enabled' => env('FEATURE_REVISIONS', true),
        'description' => 'Enable content revisions/history',
    ],

    'notifications' => [
        'enabled' => env('FEATURE_NOTIFICATIONS', true),
        'description' => 'Enable notifications system',
    ],

    // API Features
    'api' => [
        'enabled' => env('FEATURE_API', true),
        'description' => 'Enable public API',
    ],

    'api_docs' => [
        'enabled' => env('FEATURE_API_DOCS', true),
        'description' => 'Enable API documentation',
        'depends_on' => ['api'],
        'environments' => ['local', 'staging'],
    ],

    // Admin Features
    'admin_dashboard' => [
        'enabled' => true,
        'description' => 'Enable admin dashboard',
    ],

    'activity_log' => [
        'enabled' => env('FEATURE_ACTIVITY_LOG', true),
        'description' => 'Enable activity logging',
    ],

    'user_management' => [
        'enabled' => true,
        'description' => 'Enable user management',
    ],

    'role_permissions' => [
        'enabled' => true,
        'description' => 'Enable role-based permissions',
    ],

    // Development Features
    'debug_bar' => [
        'enabled' => env('FEATURE_DEBUG_BAR', false),
        'environments' => ['local'],
        'description' => 'Enable debug bar',
    ],

    'telescope' => [
        'enabled' => env('FEATURE_TELESCOPE', false),
        'environments' => ['local', 'staging'],
        'description' => 'Enable Laravel Telescope',
    ],
];
