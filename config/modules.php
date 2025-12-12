<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    |
    | This is the path where modules are stored. By default, modules are
    | stored in the "modules" directory in the root of your application.
    |
    */
    'path' => base_path('modules'),

    /*
    |--------------------------------------------------------------------------
    | Module Stubs Path
    |--------------------------------------------------------------------------
    |
    | This is the path where module stubs are stored for scaffolding.
    |
    */
    'stubs_path' => base_path('stubs/module'),

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for all modules.
    |
    */
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, modules will be automatically discovered and loaded.
    |
    */
    'auto_discovery' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Cache configuration for modules.
    |
    */
    'cache' => [
        'enabled' => env('MODULES_CACHE_ENABLED', true),
        'driver' => env('MODULES_CACHE_DRIVER', 'file'),
        'ttl' => env('MODULES_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Structure
    |--------------------------------------------------------------------------
    |
    | Define the directory structure for modules.
    |
    */
    'structure' => [
        'config' => 'Config',
        'console' => 'Console/Commands',
        'controllers' => 'Http/Controllers',
        'middleware' => 'Http/Middleware',
        'requests' => 'Http/Requests',
        'resources' => 'Http/Resources',
        'database' => 'Database',
        'migrations' => 'Database/Migrations',
        'seeders' => 'Database/Seeders',
        'factories' => 'Database/Factories',
        'models' => 'Domain/Models',
        'services' => 'Services',
        'repositories' => 'Repositories',
        'contracts' => 'Contracts',
        'events' => 'Events',
        'listeners' => 'Listeners',
        'jobs' => 'Jobs',
        'policies' => 'Policies',
        'traits' => 'Traits',
        'exceptions' => 'Exceptions',
        'providers' => 'Providers',
        'routes' => 'Routes',
        'views' => 'Resources/views',
        'lang' => 'Resources/lang',
        'assets' => 'Resources/assets',
        'tests' => 'Tests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | Module activators configuration.
    |
    */
    'activators' => [
        'file' => [
            'class' => \App\Support\Module::class,
            'statuses_file' => base_path('modules_statuses.json'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Modules
    |--------------------------------------------------------------------------
    |
    | Modules that cannot be disabled.
    |
    */
    'core_modules' => [
        'core',
        'users',
        'auth',
    ],
];
