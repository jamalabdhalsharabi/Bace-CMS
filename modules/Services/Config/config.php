<?php

return [
    'name' => 'Services',

    'statuses' => [
        'draft',
        'pending_review',
        'in_review',
        'approved',
        'rejected',
        'published',
        'scheduled',
        'unpublished',
        'archived',
    ],

    'workflow' => [
        'require_review' => true,
        'auto_publish_on_approve' => false,
    ],

    'revisions' => [
        'enabled' => true,
        'max_revisions' => 50,
    ],

    'media' => [
        'max_images' => 20,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
