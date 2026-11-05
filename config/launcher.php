<?php

return [
    // Public installer used by moddingway.ir/launcher. Keep this separate
    // from LAUNCHER_DOWNLOAD_URL which points to an updater package.
    'public_download_url' => env('LAUNCHER_PUBLIC_DOWNLOAD_URL'),

    'max_active_devices_per_user' => (int)env('LAUNCHER_MAX_ACTIVE_DEVICES', 2),
    'token_ttl_days' => (int)env('LAUNCHER_TOKEN_TTL_DAYS', 30),
    'checkout_session_ttl_minutes' => (int)env('LAUNCHER_CHECKOUT_SESSION_TTL_MINUTES', 30),
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

    // Release metadata is shared by every bootstrap request for a platform/channel.
    // Model events invalidate it immediately; this TTL is a safety net for changes
    // made outside Eloquent (for example, a manual database update).
    'bootstrap_cache_ttl_seconds' => max(
        1,
        (int) env('LAUNCHER_BOOTSTRAP_CACHE_TTL_SECONDS', 3600)
    ),

    'download_url_ttl_minutes' => env(
        'LAUNCHER_DOWNLOAD_URL_TTL_MINUTES',
        60
    ),

    // For mods/updates, return the download host URL from prepare immediately.
    // When disabled, the signed session endpoint revalidates access and redirects
    // to that host. Product downloads always use the signed session endpoint.
    // File bytes are served by the download host in both modes.
    // Without secure_link_secret, the destination URL remains usable until moved.
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

    // Shared secret for nginx's secure_link module on the download host. When set, a
    // direct URL is handed out signed and time-limited instead of permanent, and
    // carries an opaque per-user token so the download host's access log attributes
    // every byte to an account -- which is what makes a leak actionable rather than
    // merely regrettable. Empty (the default) leaves URLs unsigned, exactly as before.
    //
    // The nginx side must be configured as:
    //     secure_link        $arg_md5,$arg_expires;
    //     secure_link_md5    "SECRET$secure_link_expires$uri$arg_u";
    // with SECRET being this same value. Note the deliberate absence of $remote_addr:
    // including it is the common recipe, but it breaks resume for mobile and CGNAT
    // users whose address changes mid-download -- and an 80 GB download on an ordinary
    // connection lasts long enough for that to be routine, not rare.
    'secure_link_secret' => (string)env('LAUNCHER_SECURE_LINK_SECRET', ''),

    // How long a signed URL stays valid. This does not cap download duration: the
    // launcher treats 403/410 as "refresh the link" and resumes from its existing
    // partial file (see IsRefreshableDownloadStatus in LaravelResilientDownloadService),
    // so a 36-hour download over a slow line simply re-signs a few times. Keep it short
    // enough that a link pasted into a group is dead before most people click it.
    'secure_link_ttl_minutes' => max(5, (int)env('LAUNCHER_SECURE_LINK_TTL_MINUTES', 240)),

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

    /*
     * When false (default), background verification is a single HEAD request that checks
     * the remote size against the registered one and records the registered SHA-256.
     * When true, every file is streamed in full through the web server and hashed --
     * minutes per multi-gigabyte part and the whole archive's worth of traffic per run.
     * Only enable temporarily to audit registered checksums.
     */
    'download_integrity_full_hash' => (bool)env('LAUNCHER_DOWNLOAD_INTEGRITY_FULL_HASH', false),

    'remote_download_connect_timeout_seconds' => (int)env(
        'LAUNCHER_REMOTE_DOWNLOAD_CONNECT_TIMEOUT_SECONDS',
        15
    ),
];
