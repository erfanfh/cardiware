<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\Cache;

final class GetStoreDetailAction
{
    public function execute(Store $store): Store
    {
        return Cache::tags([
            CacheTag::STORES,
            CacheTag::STORE_DETAIL,
            CacheTag::store($store->getKey()),
        ])->remember(
            CacheKey::storeDetail(
                $store->getKey(),
                $store->updated_at?->timestamp ?? 0
            ),
            CacheTtl::long(),
            fn() => $store
                ->loadMissing([
                    'picture.primaryVariant',
                    'picture.variants',
                ])
                ->loadCount([
                    'products',
                ])
        );
    }
}
