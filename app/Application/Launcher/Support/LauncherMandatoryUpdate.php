<?php

namespace App\Application\Launcher\Support;

use Illuminate\Support\Carbon;

/**
 * «آپدیت الزامی» enforced on the server.
 *
 * force_update used to reach the launcher only through the bootstrap response, which a
 * launcher reads once at start: one that stayed open (or sat next to the Windows clock for
 * days) kept working on the old version after the admin made a newer one mandatory. The
 * launcher.account middleware now refuses authenticated launcher requests from such versions.
 * Same rule as GetLauncherBootstrapAction: any published, enabled, mandatory release newer than
 * the client, or a client below the latest release's minimum_supported_version.
 */
final class LauncherMandatoryUpdate
{
    public function __construct(private readonly LauncherReleaseCache $releaseCache)
    {
    }

    /** The version the client must install, or null when it may continue. */
    public function requiredVersion(?string $clientVersion): ?string
    {
        $clientVersion = trim((string) $clientVersion);
        // No/odd header (browser, tools, very old builds): never block on a guess.
        if (preg_match('/^\d+(\.\d+){1,3}$/', $clientVersion) !== 1) {
            return null;
        }

        $now = Carbon::now()->getTimestamp();
        $published = collect($this->releaseCache->get('windows', 'stable'))
            ->filter(static fn (array $release): bool => is_int($release['published_at_timestamp'] ?? null)
                && $release['published_at_timestamp'] <= $now
                && is_string($release['version'] ?? null))
            ->sort(static fn (array $a, array $b): int => version_compare($a['version'], $b['version']))
            ->values();
        if ($published->isEmpty()) {
            return null;
        }

        $latest = $published->last();
        $mandatory = $published
            ->filter(static fn (array $release): bool => (bool) ($release['is_mandatory'] ?? false)
                && version_compare($clientVersion, $release['version'], '<'))
            ->last();
        if ($mandatory !== null) {
            return $latest['version'];
        }

        $minimum = trim((string) ($latest['minimum_supported_version'] ?? ''));
        if ($minimum !== '' && version_compare($clientVersion, $minimum, '<')) {
            return $latest['version'];
        }

        return null;
    }
}
