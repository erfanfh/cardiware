<?php

namespace App\Application\Launcher\Actions;

use Illuminate\Support\Carbon;
use RuntimeException;

class GetLauncherBootstrapAction
{
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

        $latestVersion = (string)$configuration['latest_version'];
        $minimumSupportedVersion = (string)$configuration['minimum_supported_version'];

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

        $forceUpdate = (bool)config('launcher.force_update', false)
            || $minimumVersionNotSatisfied;

        $maintenanceEnabled = (bool)config(
            'launcher.maintenance.enabled',
            false
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
                'message' => $maintenanceEnabled
                    ? config('launcher.maintenance.message')
                    : null,
            ],

            'download_url' => $configuration['download_url'] ?? null,

            'server_time' => Carbon::now()->toISOString(),
        ];
    }
}
