<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profiles Path
    |--------------------------------------------------------------------------
    |
    | The path where profile YAML files are stored.
    |
    */
    'path' => base_path('config/profiles'),

    /*
    |--------------------------------------------------------------------------
    | Default Profile
    |--------------------------------------------------------------------------
    |
    | The default profile to use when no profile is specified.
    |
    */
    'default' => env('APP_PROFILE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Auto Apply
    |--------------------------------------------------------------------------
    |
    | Whether to automatically apply the default profile on boot.
    |
    */
    'auto_apply' => env('PROFILE_AUTO_APPLY', false),
];
