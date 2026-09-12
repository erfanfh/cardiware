<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

/**
 * Flushes the launcher API read cache. Called from the same places that already bump
 * manifest revisions (BumpLauncherManifestRevisionAction) and from LauncherCacheObserver
 * for the models that are not covered by a revision bump (portal content, purchase
 * accesses, products, devices).
 */
final class LauncherCacheInvalidator
{
    /** Anything derived from a sale item: catalog entries, manifest, update/mod lists. */
    public function flushSaleItem(int|string $saleItemId): void
    {
        Cache::tags([CacheTag::launcherSaleItem($saleItemId)])->flush();
        // Catalog pages embed many sale items; a change to one invalidates every page.
        $this->flushCatalog();
        // Library rows carry the sale item's file list.
        $this->flushLibraryAll();
    }

    public function flushCatalog(): void
    {
        Cache::tags([CacheTag::LAUNCHER_CATALOG])->flush();
    }

    public function flushLibraryForUser(int|string $userId): void
    {
        Cache::tags([CacheTag::launcherUser($userId)])->flush();
    }

    public function flushLibraryAll(): void
    {
        Cache::tags([CacheTag::LAUNCHER_LIBRARY])->flush();
    }

    public function flushPortal(): void
    {
        Cache::tags([CacheTag::LAUNCHER_PORTAL])->flush();
    }

    public function flushDevice(int|string $deviceId): void
    {
        Cache::tags([CacheTag::launcherDevice($deviceId)])->flush();
    }

    public function flushAll(): void
    {
        Cache::tags([CacheTag::LAUNCHER])->flush();
    }
}
