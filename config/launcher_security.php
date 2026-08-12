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
];
