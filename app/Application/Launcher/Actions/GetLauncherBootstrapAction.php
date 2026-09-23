<?php

namespace App\Application\Launcher\Actions;

use App\Application\Launcher\Support\LauncherReleaseCache;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Support\Carbon;
use RuntimeException;

class GetLauncherBootstrapAction
{
    public function __construct(
        private readonly GeneralSettings $settings,
        private readonly LauncherReleaseCache $releaseCache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(
        string $currentVersion,
        string $platform,
        string $channel,
        ?string $deviceId = null,
    ): array {
        $configuration = config(
            "launcher.{$platform}.{$channel}"
        );

        if (! is_array($configuration)) {
            throw new RuntimeException(
                'Launcher configuration was not found.'
            );
        }

        $now = Carbon::now();
        $publishedReleases = collect($this->releaseCache->get($platform, $channel))
            ->filter(static fn (array $candidate): bool => is_int($candidate['published_at_timestamp'] ?? null)
                && $candidate['published_at_timestamp'] <= $now->getTimestamp())
            ->sort(static fn (array $left, array $right): int => version_compare($left['version'], $right['version']))
            ->values();

        /** @var array<string, mixed>|null $release */
        $release = $publishedReleases->last();

        $latestVersion = ($release['version'] ?? null)
            ?: (string) $configuration['latest_version'];
        $minimumSupportedVersion = ($release['minimum_supported_version'] ?? null)
            ?: (string) $configuration['minimum_supported_version'];

        $updateAvailable = version_compare(
            $currentVersion,
            $latestVersion,
            '<'
        );

        $minimumVersionNotSatisfied = version_compare(
            $currentVersion,
            $minimumSupportedVersion,
            '<'
        );

        $mandatoryReleasePending = $publishedReleases->contains(
            static fn (array $candidate): bool => $candidate['is_mandatory']
                && version_compare($currentVersion, $candidate['version'], '<')
        );

        $forceUpdate = ((bool) config('launcher.force_update', false) && $updateAvailable)
            || $mandatoryReleasePending
            || $minimumVersionNotSatisfied;

        // Admin panel ("مدیریت آپدیت لانچر") controls this at runtime via the settings table;
        // the LAUNCHER_MAINTENANCE_* env vars only serve as the fallback for a deployment where
        // nobody has touched this panel yet.
        $maintenanceEnabled = (bool) $this->settings->get(
            'launcher_maintenance_enabled',
            config('launcher.maintenance.enabled', false)
        );
        $maintenanceMessage = $this->settings->get(
            'launcher_maintenance_message',
            config('launcher.maintenance.message')
        );

        // A device whose owning account is on the bypass list keeps normal access
        // even while maintenance is on -- lets the team run a real build against the
        // real server before lifting maintenance for everyone. $deviceId is not a
        // credential (see DeviceIdentityService on the client): it only identifies
        // WHICH already-registered device is asking, never authenticates it, so this
        // can only ever narrow a block, never grant anything beyond "see the normal
        // bootstrap response instead of the maintenance message."
        if ($maintenanceEnabled && $this->deviceBypassesMaintenance($deviceId)) {
            $maintenanceEnabled = false;
        }

        return [
            'platform' => $platform,
            'channel' => $channel,

            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'minimum_supported_version' => $minimumSupportedVersion,

            'update_available' => $updateAvailable,
            'force_update' => $forceUpdate,

            'maintenance' => [
                'enabled' => $maintenanceEnabled,
                'message' => $maintenanceEnabled ? $maintenanceMessage : null,
            ],

            'download_url' => ($release['download_url'] ?? null)
                ?: ($configuration['download_url'] ?? null),
            'file_name' => ($release['file_name'] ?? null)
                ?: ($configuration['file_name'] ?? null),
            'file_size_bytes' => ($release['size_bytes'] ?? null)
                ?: ($configuration['file_size_bytes'] ?? null),
            'sha256' => ($release['sha256'] ?? null)
                ?: ($configuration['sha256'] ?? null),
            'signature' => ($release['signature'] ?? null)
                ?: ($configuration['signature'] ?? null),
            'signature_key_id' => ($release['signature_key_id'] ?? null)
                ?: ($configuration['signature_key_id'] ?? null),
            'release_notes' => ($release['release_notes'] ?? null)
                ?: ($configuration['release_notes'] ?? null),
            'release_published_at' => $release['published_at'] ?? null,

            // Public full-installer link (admin "launcher public download URL"). The launcher
            // offers it as a fallback when its in-place updater cannot run (antivirus etc.).
            'setup_download_url' => trim((string) $this->settings->get(
                'launcher_public_download_url',
                config('launcher.public_download_url')
            )) ?: null,
            // Per-file parallel connection cap for product/update/mod downloads. Shared
            // download hosts (cPanel/LiteSpeed) count concurrent connections per vhost and
            // suspend the account when hundreds of long-lived range requests pile up --
            // 8 per user was enough to get dl4 blocked. Tunable from the panel without a
            // launcher release; the launcher clamps it to 1..8.
            'download_max_connections' => max(1, min(8, (int) $this->settings->get(
                'launcher_download_max_connections',
                8
            ))),
            'server_time' => $now->toISOString(),
        ];
    }

    private function deviceBypassesMaintenance(?string $deviceId): bool
    {
        $deviceId = trim((string) $deviceId);

        if ($deviceId === '') {
            return false;
        }

        $bypassMobiles = $this->parseBypassMobiles(
            (string) $this->settings->get('launcher_maintenance_bypass_mobiles', '')
        );

        if ($bypassMobiles === []) {
            return false;
        }

        $device = LauncherDevice::query()
            ->where('device_id', $deviceId)
            ->whereNull('revoked_at')
            ->with('user:id,mobile')
            ->first();

        $mobile = $device?->user?->mobile;

        return $mobile !== null && in_array($mobile, $bypassMobiles, true);
    }

    /**
     * One mobile number per line in the admin textarea. Silently drops blank/
     * malformed lines rather than rejecting the whole list -- this is a small,
     * low-stakes admin-typed field (see the protected_* path lists on the
     * product manifest for the same normalize-don't-reject convention), and a
     * typo'd line should not block the ones that are fine.
     *
     * @return list<string>
     */
    private function parseBypassMobiles(string $raw): array
    {
        $numbers = [];

        foreach (preg_split('/\R/', $raw) ?: [] as $line) {
            $line = (string) normalize_number(trim($line));

            if (preg_match('/^09\d{9}$/', $line) === 1) {
                $numbers[] = $line;
            }
        }

        return array_values(array_unique($numbers));
    }
}
