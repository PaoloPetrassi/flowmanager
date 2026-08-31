<?php

return [
    'version' => env('FLOWMANAGER_VERSION', '1.0.0'),

    'admin' => [
        'name' => env('FLOWMANAGER_ADMIN_NAME', 'FlowManager Administrator'),
        'email' => env('FLOWMANAGER_ADMIN_EMAIL', 'admin@flowmanager.test'),
        'password' => env('FLOWMANAGER_ADMIN_PASSWORD'),
    ],

    'security' => [
        'two_factor_required_for_administrators' => (bool) env('FLOWMANAGER_2FA_REQUIRED_FOR_ADMINS', false),
        'require_email_verification' => (bool) env('FLOWMANAGER_REQUIRE_EMAIL_VERIFICATION', false),
    ],

    'notifications' => [
        'mail_enabled' => (bool) env('FLOWMANAGER_MAIL_NOTIFICATIONS', false),
    ],

    'sla' => [
        'hours' => [
            'low' => (int) env('FLOWMANAGER_SLA_LOW_HOURS', 48),
            'medium' => (int) env('FLOWMANAGER_SLA_MEDIUM_HOURS', 24),
            'high' => (int) env('FLOWMANAGER_SLA_HIGH_HOURS', 8),
            'urgent' => (int) env('FLOWMANAGER_SLA_URGENT_HOURS', 4),
        ],
    ],

    'backups' => [
        'keep' => (int) env('FLOWMANAGER_BACKUPS_KEEP', 14),
    ],

    'jobs' => [
        'history_days' => (int) env('FLOWMANAGER_JOB_HISTORY_DAYS', 30),
    ],

    'queues' => [
        'imports' => env('FLOWMANAGER_QUEUE_IMPORTS', 'imports'),
        'webhooks' => env('FLOWMANAGER_QUEUE_WEBHOOKS', 'webhooks'),
        'reports' => env('FLOWMANAGER_QUEUE_REPORTS', 'reports'),
        'system' => env('FLOWMANAGER_QUEUE_SYSTEM', 'system'),
    ],
];
