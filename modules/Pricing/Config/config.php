<?php

return [
    'name' => 'Pricing',

    'plan_types' => ['subscription', 'one_time', 'usage_based'],

    'billing_periods' => ['monthly', 'quarterly', 'yearly', 'lifetime'],

    'statuses' => ['draft', 'active', 'inactive', 'archived'],

    'subscription_statuses' => ['pending', 'trial', 'active', 'paused', 'expired', 'cancelled'],

    'trial_days' => 14,

    'grace_period_days' => 3,

    'retry_attempts' => 3,
];
