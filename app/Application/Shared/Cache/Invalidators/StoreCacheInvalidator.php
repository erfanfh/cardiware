<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\Cache;

class StoreCacheInvalidator
{
    public function __construct(
        protected SeoCacheInvalidator  $seoCacheInvalidator,
        protected HomeCacheInvalidator $homeCacheInvalidator,
    )
    {
    }

    public function afterCreated(Store $store): void
    {
        $this->flushStoreRelatedCaches($store);
    }

    public function afterUpdated(Store $store): void
    {
        $this->flushStoreRelatedCaches($store);
    }

    public function afterDeleted(Store $store): void
    {
        $this->flushStoreRelatedCaches($store);
    }

    protected function flushStoreRelatedCaches(Store $store): void
    {
        $this->flushStoreSeoCaches($store);
        $this->flushStoreDetailCaches($store);
        $this->forgetStoreProductDetails($store);
    }

    protected function forgetStoreProductDetails(Store $store): void
    {
        Product::query()
            ->withoutGlobalScope(ActiveProductScope::class)
            ->where('store_id', $store->id)
            ->select(['id', 'updated_at'])
            ->each(function (Product $product): void {
                Cache::tags([CacheTag::PRODUCT_DETAIL, CacheTag::PRODUCTS])
                    ->forget(CacheKey::productDetail($product->id, $product->updated_at->timestamp));
            });
    }

    protected function flushStoreSeoCaches(Store $store): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::STORES,
            CacheTag::store($store->id),
        ])->flush();
    }

    protected function flushStoreDetailCaches(Store $store): void
    {
        Cache::tags([
            CacheTag::STORE_DETAIL,
            CacheTag::store($store->id),
        ])->flush();
    }
}
