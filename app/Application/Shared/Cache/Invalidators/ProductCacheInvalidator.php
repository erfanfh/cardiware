<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductCacheInvalidator
{
    public function __construct(
        protected SeoCacheInvalidator  $seoCacheInvalidator,
        protected HomeCacheInvalidator $homeCacheInvalidator,
    )
    {
    }

    public function afterCreated(Product $product): void
    {
        $this->flushProductRelatedCaches($product);
    }

    public function afterUpdated(Product $product): void
    {
        $this->flushProductRelatedCaches($product);
    }

    public function afterDeleted(Product $product): void
    {
        $this->flushProductRelatedCaches($product);
    }

    protected function flushProductRelatedCaches(Product $product): void
    {
        $this->flushProductSeoCaches($product);
        $this->flushProductDetailCaches($product);
        $this->flushProductListCaches();
        $this->flushProductHomeCaches();
        $this->flushProductStoreCaches($product);
    }

    protected function flushProductSeoCaches(Product $product): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::PRODUCTS,
            CacheTag::product($product->id),
        ])->flush();
    }

    protected function flushProductDetailCaches(Product $product): void
    {
        Cache::tags([
            CacheTag::PRODUCT_DETAIL,
            CacheTag::PRODUCT_STATS,
            CacheTag::PRODUCT_BUY_BOX,
            CacheTag::CATEGORIES,
            CacheTag::product($product->id),
        ])->flush();
    }

    public function flushProductListCaches(): void
    {
        Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
        ])->flush();
    }

    protected function flushProductHomeCaches(): void
    {
        Cache::tags([
            CacheTag::HOME,
            CacheTag::HOME_PRODUCTS,
        ])->flush();
    }

    protected function flushProductStoreCaches(Product $product): void
    {
        Cache::tags([
            CacheTag::STORE_PRODUCTS,
            CacheTag::store($product->store->id),
        ])->flush();
    }
}
