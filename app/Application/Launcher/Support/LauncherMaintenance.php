<?php

namespace App\Application\Launcher\Support;

use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;

/**
 * «حالت تعمیر لانچر» enforced on the server.
 *
 * It used to exist only in the bootstrap response, which the launcher reads once at start:
 * login itself never checked it, and a launcher that was already open kept working. It is
 * now applied to the launcher login endpoints and to every authenticated launcher request
 * (via the launcher.account middleware), so it holds for every launcher version.
 *
 * Accounts whose mobile is on the bypass list keep full access, as before.
 */
final class LauncherMaintenance
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function isEnabled(): bool
    {
        return (bool)$this->settings->get(
            'launcher_maintenance_enabled',
            config('launcher.maintenance.enabled', false)
        );
    }

    public function message(): string
    {
        $message = trim((string)$this->settings->get(
            'launcher_maintenance_message',
            config('launcher.maintenance.message')
        ));

        return $message !== '' ? $message : 'سرویس لانچر موقتاً در حال تعمیر و به‌روزرسانی است؛ کمی بعد دوباره تلاش کنید.';
    }

    /** True when maintenance is on and this mobile/user is NOT on the bypass list. */
    public function blocks(?User $user = null, ?string $mobile = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $mobile = $user?->mobile ?? $mobile;
        $normalized = $mobile === null ? '' : (string)normalize_number(trim($mobile));

        return $normalized === '' || !in_array($normalized, $this->bypassMobiles(), true);
    }

    /** @return list<string> */
    private function bypassMobiles(): array
    {
        $numbers = [];
        foreach (preg_split('/\R/', (string)$this->settings->get('launcher_maintenance_bypass_mobiles', '')) ?: [] as $line) {
            $line = (string)normalize_number(trim($line));
            if (preg_match('/^09\d{9}$/', $line) === 1) {
                $numbers[] = $line;
            }
        }

        return array_values(array_unique($numbers));
    }
}
