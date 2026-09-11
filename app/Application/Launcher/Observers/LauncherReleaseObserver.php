<?php

namespace App\Application\Launcher\Observers;

use App\Application\Launcher\Support\LauncherReleaseCache;
use App\Domain\Launcher\Models\LauncherRelease;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class LauncherReleaseObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly LauncherReleaseCache $cache,
    ) {}

    public function created(LauncherRelease $release): void
    {
        $this->forget($release->platform, $release->channel);
    }

    public function updated(LauncherRelease $release): void
    {
        $this->forget($release->platform, $release->channel);

        if ($release->wasChanged(['platform', 'channel'])) {
            $this->forget(
                $release->getRawOriginal('platform'),
                $release->getRawOriginal('channel'),
            );
        }
    }

    public function deleted(LauncherRelease $release): void
    {
        $this->forget($release->platform, $release->channel);
    }

    private function forget(mixed $platform, mixed $channel): void
    {
        if (! is_string($platform) || ! is_string($channel)) {
            return;
        }

        $this->cache->forget($platform, $channel);
    }
}
