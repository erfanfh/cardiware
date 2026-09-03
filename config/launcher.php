<?php

return [
    // Public installer used by moddingway.ir/launcher. Keep this separate
    // from LAUNCHER_DOWNLOAD_URL which points to an updater package.
    'public_download_url' => env('LAUNCHER_PUBLIC_DOWNLOAD_URL'),

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
            'signature' => env('LAUNCHER_UPDATE_SIGNATURE'),
            'signature_key_id' => env('LAUNCHER_UPDATE_SIGNATURE_KEY_ID'),
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
        60
    ),

    // Hand the launcher the download host's own URL instead of a signed URL that
    // streams through this server.
    //
    // With proxying, every byte of every product download passes through PHP: each
    // of the launcher's 8 parallel range requests holds one PHP-FPM worker for the
    // whole transfer. A few hundred people downloading an 80 GB product at once
    // needs thousands of concurrent workers, which no ordinary VPS has -- and
    // because that worker pool also serves the website, exhausting it takes the
    // entire site down, not just downloads. The bandwidth is the second problem:
    // the same traffic would have to leave this VPS as well as arrive at it.
    //
    // Going direct removes both. Authorization is unchanged and still happens here
    // before any URL is issued (ownership, device, session, manifest revision); only
    // the byte transfer moves off this server.
    //
    // What it gives up: the real URL becomes visible to the launcher, and a leaked
    // URL stays usable until the file is moved. That is an acceptable trade because
    // the URL was never the thing protecting the content -- the archive password and
    // the content key are, and neither is obtainable from the URL. Rotating the
    // upload folder per release keeps a leaked link short-lived.
    'direct_download_enabled' => (bool)env('LAUNCHER_DIRECT_DOWNLOAD', false),

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

    // file_name in the admin manifest must be the exact physical file name at
    // the end of the source URL. Disable only for providers with opaque URLs.
    'require_source_file_name_match' => (bool)env(
        'LAUNCHER_REQUIRE_SOURCE_FILE_NAME_MATCH',
        true
    ),

    /*
     * Renewable server-side lease. It is not the maximum duration of a large
     * download. Every authenticated prepare request revalidates the purchase,
     * active device and manifest before extending this lease.
     */
    'download_session_lease_minutes' => (int)env(
        'LAUNCHER_DOWNLOAD_SESSION_LEASE_MINUTES',
        10080
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
