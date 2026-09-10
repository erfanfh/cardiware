<?php

namespace App\Application\Launcher\Actions;

use App\Application\Launcher\Support\LauncherReleaseCache;
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

            'server_time' => $now->toISOString(),
        ];
    }
}
