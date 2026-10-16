<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSlug;
use Illuminate\Support\Facades\Cache;

final class StoreSlugCacheInvalidator
{
    public function forgetBySlug(string $slug): void
    {
        if (trim($slug) !== '') {
            Cache::forget(CacheKey::storeSlug($slug));
        }
    }

    public function forgetForModel(Store $store): void
    {
        $slugs = StoreSlug::query()
            ->where('store_id', $store->getKey())
            ->pluck('route_key')
            ->push($store->slug)
            ->filter()
            ->unique();

        foreach ($slugs as $slug) {
            $this->forgetBySlug($slug);
        }
    }
}
