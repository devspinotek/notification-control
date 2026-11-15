<?php

return [
    'route' => [
        'prefix' => 'backend/notification-control',
        'name_prefix' => 'admin.notification-control.',
        'middleware' => ['web', 'auth', 'role:super admin'],
    ],

    // Nama Blade component layout host (mis. 'backend-layout'), atau null untuk standalone layout bawaan package.
    'layout' => null,

    'dispatch' => [
        'mode' => env('MAIL_DISPATCH_MODE', 'async'),
        'sandbox' => [
            // Jika diisi dan !app()->isProduction(), seluruh email keluar dibelokkan ke alamat ini.
            'redirect_email_to' => env('NOTIFICATION_SANDBOX_EMAIL'),
        ],
    ],

    'channels' => [
        'mail' => ['label' => 'Email', 'icon' => 'fas fa-envelope'],
        'database' => ['label' => 'In-App', 'icon' => 'fas fa-bell'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'],
    ],

    'discovery_paths' => [
        app_path('Mail'),
        app_path('Notifications'),
    ],

    'policy_storage_path' => storage_path('app/notification-control-policies.json'),

    'activity_log' => [
        'enabled' => true,
        'max_entries' => 50,
        'cache_key' => 'notification_control.recent_activity',
    ],

    'catalog' => [],
];

