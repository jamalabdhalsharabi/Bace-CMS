<?php

return [
    'name' => 'Forms',

    'types' => ['contact', 'newsletter', 'survey', 'custom'],

    'field_types' => [
        'text', 'email', 'textarea', 'select', 
        'checkbox', 'radio', 'file', 'date', 
        'number', 'phone', 'url', 'hidden',
    ],

    'statuses' => ['new', 'read', 'spam', 'processed'],

    'spam_detection' => [
        'enabled' => true,
        'honeypot' => true,
        'rate_limit' => 5,
        'rate_limit_period' => 60,
    ],

    'captcha' => [
        'driver' => env('CAPTCHA_DRIVER', 'recaptcha'),
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'notifications' => [
        'enabled' => true,
        'queue' => 'default',
    ],

    'uploads' => [
        'disk' => 'public',
        'path' => 'form-uploads',
        'max_size' => 5120,
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'png'],
    ],
];
