<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
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
