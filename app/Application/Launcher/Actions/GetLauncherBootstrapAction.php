<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Launcher\Models\LauncherRelease;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class GetLauncherBootstrapAction
{
    public function __construct(
        private readonly GeneralSettings $settings,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(
        string $currentVersion,
        string $platform,
        string $channel,
    ): array
    {
        $configuration = config(
            "launcher.{$platform}.{$channel}"
        );

        if (!is_array($configuration)) {
            throw new RuntimeException(
                'Launcher configuration was not found.'
            );
        }

        $publishedReleases = Schema::hasTable('launcher_releases')
            ? LauncherRelease::query()
                ->published()
                ->where('platform', $platform)
                ->where('channel', $channel)
                ->get()
                ->sort(static fn(LauncherRelease $left, LauncherRelease $right): int =>
                    version_compare($left->version, $right->version))
                ->values()
            : collect();

        /** @var LauncherRelease|null $release */
        $release = $publishedReleases->last();

        $latestVersion = $release?->version
            ?: (string)$configuration['latest_version'];
        $minimumSupportedVersion = $release?->minimum_supported_version
            ?: (string)$configuration['minimum_supported_version'];

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
            static fn(LauncherRelease $candidate): bool =>
                $candidate->is_mandatory
                && version_compare($currentVersion, $candidate->version, '<')
        );

        $forceUpdate = ((bool)config('launcher.force_update', false) && $updateAvailable)
            || $mandatoryReleasePending
            || $minimumVersionNotSatisfied;

        // Admin panel ("مدیریت آپدیت لانچر") controls this at runtime via the settings table;
        // the LAUNCHER_MAINTENANCE_* env vars only serve as the fallback for a deployment where
        // nobody has touched this panel yet.
        $maintenanceEnabled = (bool)$this->settings->get(
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

            'download_url' => $release?->download_url
                ?: ($configuration['download_url'] ?? null),
            'file_name' => $release?->file_name
                ?: ($configuration['file_name'] ?? null),
            'file_size_bytes' => $release?->size_bytes
                ?: ($configuration['file_size_bytes'] ?? null),
            'sha256' => $release?->sha256
                ?: ($configuration['sha256'] ?? null),
            'signature' => $release?->signature
                ?: ($configuration['signature'] ?? null),
            'signature_key_id' => $release?->signature_key_id
                ?: ($configuration['signature_key_id'] ?? null),
            'release_notes' => $release?->release_notes
                ?: ($configuration['release_notes'] ?? null),
            'release_published_at' => $release?->published_at?->toISOString(),

            'server_time' => Carbon::now()->toISOString(),
        ];
    }
}
