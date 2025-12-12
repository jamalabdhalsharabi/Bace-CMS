<?php

return [
    'name' => 'Notifications',

    'channels' => ['database', 'mail', 'broadcast'],

    'default_channel' => 'database',

    'per_page' => 20,

    'auto_mark_read_on_view' => false,

    'cleanup' => [
        'enabled' => true,
        'days' => 30,
    ],
];
