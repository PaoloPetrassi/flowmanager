<?php

return [
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
];
