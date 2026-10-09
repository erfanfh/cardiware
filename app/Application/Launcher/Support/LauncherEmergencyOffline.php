<?php

namespace App\Application\Launcher\Support;

use App\Infrastructure\Services\Setting\Groups\GeneralSettings;

/**
 * «حالت اضطراری قطعی اینترنت» -- set in the launcher-release admin page before an expected
 * internet shutdown (including Iran's domestic network). While it is on:
 *   - every offline licence issued is valid for `launcher_emergency_offline_days` (8..30)
 *     instead of the normal 7, so users who come online once before the cut can keep
 *     playing through it;
 *   - moving an account to a different device is refused, so the long licence cannot be
 *     used to run the same account on a second machine for a month.
 * Switching it off returns to normal; licences already issued keep their own expiry.
 */
final class LauncherEmergencyOffline
{
    public const NORMAL_DAYS = 7;
    public const MIN_DAYS = 8;
    public const MAX_DAYS = 30;

    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function isEnabled(): bool
    {
        return (bool)$this->settings->get('launcher_emergency_offline_enabled', false);
    }

    public function configuredDays(): int
    {
        return max(self::MIN_DAYS, min(self::MAX_DAYS,
            (int)$this->settings->get('launcher_emergency_offline_days', 20)));
    }

    /** Days for an offline licence issued right now. */
    public function licenceDays(): int
    {
        if ($this->isEnabled()) {
            return $this->configuredDays();
        }

        return min(self::NORMAL_DAYS, max(1, (int)config('launcher_security.offline_license_days', self::NORMAL_DAYS)));
    }
}
