<?php

return [
    'name' => 'Webhooks',
    
    'timeout' => 30,
    'retry_times' => 3,
    'retry_delay' => 60,
    
    'events' => [
        'content.created',
        'content.updated',
        'content.deleted',
        'content.published',
        'user.registered',
        'user.updated',
        'form.submitted',
        'order.created',
    ],

    'log_retention_days' => 30,
];
