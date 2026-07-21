<?php

return [
    'windows' => [
        'stable' => [
            'latest_version' => env('LAUNCHER_LATEST_VERSION', '1.0.0'),

            'minimum_supported_version' => env(
                'LAUNCHER_MINIMUM_SUPPORTED_VERSION',
                '1.0.0'
            ),

            'download_url' => env('LAUNCHER_DOWNLOAD_URL'),
        ],
    ],

    'maintenance' => [
        'enabled' => env('LAUNCHER_MAINTENANCE_ENABLED', false),
        'message' => env(
            'LAUNCHER_MAINTENANCE_MESSAGE',
            'سرویس موقتا در دسترس نیست.'
        ),
    ],

    'force_update' => env('LAUNCHER_FORCE_UPDATE', false),

    'download_url_ttl_minutes' => env(
        'LAUNCHER_DOWNLOAD_URL_TTL_MINUTES',
        10
    ),

    'allowed_download_hosts' => array_values(
        array_filter(
            array_map(
                'trim',
                explode(
                    ',',
                    (string)env('LAUNCHER_ALLOWED_DOWNLOAD_HOSTS', '')
                )
            )
        )
    ),

    'download_session_ttl_minutes' => (int)env(
        'LAUNCHER_DOWNLOAD_SESSION_TTL_MINUTES',
        1440
    ),
];
