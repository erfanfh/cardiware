<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

final class GetStoreProductsAction
{
    public function execute(Store $store, int $page = 1, int $perPage = 12): LengthAwarePaginator
    {
        return Cache::tags([
            CacheTag::STORES,
            CacheTag::STORE_PRODUCTS,
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_LISTS,
            CacheTag::store($store->getKey()),
        ])->remember(
            CacheKey::storeProducts(
                storeId: $store->getKey(),
                page: $page,
                perPage: $perPage
            ),
            CacheTtl::medium(),
            fn() => $store
                ->products()
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'store',
                    'saleItems',
                    'categories',
                    'tags',
                ])
                ->paginate(
                    perPage: $perPage,
                    page: $page
                )
        );
    }
}
