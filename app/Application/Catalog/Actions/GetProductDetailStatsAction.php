<?php

namespace App\Application\Catalog\Actions;

use App\Application\Commerce\Reports\ProductSalesReport;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Cache;

final class GetProductDetailStatsAction
{
    public function __construct(
        private readonly ProductSalesReport $salesReport,
    )
    {
    }

    public function execute(Product $product): array
    {
        return Cache::tags([
            CacheTag::PRODUCT_STATS,
        ])->remember(
            CacheKey::productDetailStats($product->id),
            CacheTtl::medium(),
            fn() => [
                'sold_quantity' => $this->salesReport->saleItemSummary($product->id)['sold_quantity'] ?? 0,
                'store_sold_quantity' => $this->salesReport->storeSummary($product->store_id)['sold_quantity'] ?? 0,
            ]
        );
    }
}
