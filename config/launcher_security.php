<?php

return [
    // The policy is intentionally not configurable to two simultaneous devices.
    'active_devices' => 1,
    'lifetime_transfers' => 1,
    'offline_license_days' => min(
        7,
        max(1, (int)env('LAUNCHER_OFFLINE_LICENSE_DAYS', 7))
    ),
    'device_proof_clock_skew_seconds' => max(
        60,
        (int)env('LAUNCHER_DEVICE_PROOF_CLOCK_SKEW_SECONDS', 300)
    ),

    /*
    |--------------------------------------------------------------------------
    | Content key vault
    |--------------------------------------------------------------------------
    | A SEPARATE passphrase, independent of any admin's own login, gating both
    | viewing and changing the "content_key_value" field on a product's launch
    | manifest -- because that field is a real secret (the raw key protecting
    | an encrypted game database), not an ordinary setting any admin with
    | product-edit access should be able to read or silently overwrite.
    |
    | Only the bcrypt HASH is ever stored here -- never the plaintext
    | passphrase. Generate/rotate it with:
    |   php artisan launcher:set-content-key-vault-password
    | which prints the value to put in CONTENT_KEY_VAULT_PASSWORD_HASH.
    */
    'content_key_vault_password_hash' => env('CONTENT_KEY_VAULT_PASSWORD_HASH'),

    // How long a successful vault-password entry keeps content-key edits
    // unlocked for the current admin session before it must be re-entered.
    'content_key_vault_unlock_minutes' => max(
        1,
        (int)env('CONTENT_KEY_VAULT_UNLOCK_MINUTES', 10)
    ),
];
