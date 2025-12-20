<?php

return [
    'name' => 'Comments',

    'moderation' => [
        'auto_approve' => false,
        'auto_approve_verified_users' => true,
    ],

    'guest_comments' => true,

    'require_email' => true,

    'nested_replies' => true,
    'max_depth' => 3,

    'per_page' => 20,

    'spam_detection' => true,

    'auto_hide_threshold' => 3,

    'notifications' => [
        'notify_author' => true,
        'notify_parent_commenter' => true,
    ],
];
