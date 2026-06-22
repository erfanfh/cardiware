<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Commerce\Reports\ProductSalesReport;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\Cache;

final class GetStoreDetailStatsAction
{
    public function __construct(
        private readonly ProductSalesReport $productSalesReport,
    )
    {
    }

    /**
     * @return array{
     *     sold_quantity: int
     * }
     */
    public function execute(Store $store): array
    {
        return Cache::tags([
            CacheTag::STORES,
            CacheTag::STORE_STATS,
            CacheTag::store($store->getKey()),
        ])->remember(
            CacheKey::storeDetailStats($store->getKey()),
            CacheTtl::medium(),
            fn() => $this->resolve($store)
        );
    }

    /**
     * @return array{
     *     sold_quantity: int
     * }
     */
    private function resolve(Store $store): array
    {
        $salesSummary = $this->productSalesReport->storeSummary($store->getKey());

        return [
            'sold_quantity' => $salesSummary['sold_quantity'] ?? 0,
        ];
    }
}
