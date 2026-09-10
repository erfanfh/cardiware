<?php

namespace App\Application\Launcher\Support;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Launcher\Models\LauncherRelease;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class LauncherReleaseCache
{
    /**
     * Return enabled release candidates, including future scheduled releases.
     *
     * Keeping scheduled releases in the snapshot lets the action publish them at
     * the exact configured time without requiring another database query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(string $platform, string $channel): array
    {
        $key = $this->key($platform, $channel);
        $freshSeconds = max(
            1,
            (int) config('launcher.bootstrap_cache_ttl_seconds', 3600)
        );
        $staleSeconds = $freshSeconds + max(
            30,
            min(300, $freshSeconds)
        );

        return Cache::flexible(
            $key,
            [$freshSeconds, $staleSeconds],
            fn (): array => $this->load($platform, $channel),
            ['seconds' => 30],
        );
    }

    public function forget(string $platform, string $channel): void
    {
        Cache::forget($this->key($platform, $channel));
    }

    private function key(string $platform, string $channel): string
    {
        return CacheKey::launcherBootstrapReleases(
            strtolower(trim($platform)),
            strtolower(trim($channel)),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function load(string $platform, string $channel): array
    {
        if (! Schema::hasTable('launcher_releases')) {
            return [];
        }

        return LauncherRelease::query()
            ->select([
                'version',
                'minimum_supported_version',
                'is_mandatory',
                'download_url',
                'file_name',
                'size_bytes',
                'sha256',
                'signature',
                'signature_key_id',
                'release_notes',
                'published_at',
            ])
            ->where('platform', $platform)
            ->where('channel', $channel)
            ->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->get()
            ->map(static fn (LauncherRelease $release): array => [
                'version' => $release->version,
                'minimum_supported_version' => $release->minimum_supported_version,
                'is_mandatory' => $release->is_mandatory,
                'download_url' => $release->download_url,
                'file_name' => $release->file_name,
                'size_bytes' => $release->size_bytes,
                'sha256' => $release->sha256,
                'signature' => $release->signature,
                'signature_key_id' => $release->signature_key_id,
                'release_notes' => $release->release_notes,
                'published_at' => $release->published_at?->toISOString(),
                'published_at_timestamp' => $release->published_at?->getTimestamp(),
            ])
            ->all();
    }
}
