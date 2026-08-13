<?php

return [
    'max_active_devices_per_user' => (int)env('LAUNCHER_MAX_ACTIVE_DEVICES', 2),
    'token_ttl_days' => (int)env('LAUNCHER_TOKEN_TTL_DAYS', 30),
    'checkout_session_ttl_minutes' => (int)env('LAUNCHER_CHECKOUT_SESSION_TTL_MINUTES', 15),
    'windows' => [
        'stable' => [
            'latest_version' => env('LAUNCHER_LATEST_VERSION', '1.0.0'),

            'minimum_supported_version' => env(
                'LAUNCHER_MINIMUM_SUPPORTED_VERSION',
                '1.0.0'
            ),

            'download_url' => env('LAUNCHER_DOWNLOAD_URL'),
            'file_name' => env('LAUNCHER_UPDATE_FILE_NAME'),
            'file_size_bytes' => (int)env('LAUNCHER_UPDATE_FILE_SIZE_BYTES', 0),
            'sha256' => env('LAUNCHER_UPDATE_SHA256'),
            'release_notes' => env('LAUNCHER_UPDATE_RELEASE_NOTES'),
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

    /*
     * Integrity metadata is calculated by streaming the real remote file.
     * Cache the verified result so large archives are not re-hashed for every
     * prepare request. Setting this to 0 forces verification on every request.
     */
    'download_integrity_cache_ttl_minutes' => (int)env(
        'LAUNCHER_DOWNLOAD_INTEGRITY_CACHE_TTL_MINUTES',
        10080
    ),

    'remote_download_connect_timeout_seconds' => (int)env(
        'LAUNCHER_REMOTE_DOWNLOAD_CONNECT_TIMEOUT_SECONDS',
        15
    ),
];
