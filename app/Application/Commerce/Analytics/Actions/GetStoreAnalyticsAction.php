<?php

namespace App\Application\Commerce\Analytics\Actions;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsMetricDTO;
use App\Application\Commerce\Analytics\DTOs\StoreAnalyticsDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetStoreAnalyticsAction
{
    public function __construct(
        private readonly BuildStoreSalesTrendAction $buildStoreSalesTrendAction,
    ) {
    }

    public function execute(int $storeId, AnalyticsFilterDTO $filter): StoreAnalyticsDTO
    {
        return Cache::remember(
            $this->cacheKey($storeId, $filter),
            now()->addSeconds($filter->preset->cacheTtlSeconds()),
            fn(): StoreAnalyticsDTO => $this->build($storeId, $filter),
        );
    }

    public function forget(int $storeId, AnalyticsFilterDTO $filter): void
    {
        Cache::forget($this->cacheKey($storeId, $filter));
    }

    private function build(int $storeId, AnalyticsFilterDTO $filter): StoreAnalyticsDTO
    {
        $current = $this->summary($storeId, $filter->range);
        $previous = $filter->comparisonRange
            ? $this->summary($storeId, $filter->comparisonRange)
            : null;

        $currentTrend = $this->buildStoreSalesTrendAction->execute(
            storeId: $storeId,
            range: $filter->range,
            granularity: $filter->granularity,
        );

        $comparisonTrend = $filter->comparisonRange
            ? $this->buildStoreSalesTrendAction->execute(
                storeId: $storeId,
                range: $filter->comparisonRange,
                granularity: $filter->granularity,
            )
            : null;

        return new StoreAnalyticsDTO(
            filter: $filter,
            metrics: $this->buildMetrics($current, $previous),
            salesTrend: $this->buildStoreSalesTrendAction->mergeComparison(
                current: $currentTrend,
                comparison: $comparisonTrend,
            ),
            productPerformance: $this->productPerformance(
                storeId: $storeId,
                range: $filter->range,
                totalNetSales: (int)$current['net_sales'],
            ),
            generatedAt: now()->toImmutable(),
        );
    }

    private function cacheKey(int $storeId, AnalyticsFilterDTO $filter): string
    {
        return sprintf(
            'commerce-analytics:store:v1:%d:%s',
            $storeId,
            $filter->cacheKey(),
        );
    }

    /**
     * @return array<string, int|float>
     */
    private function summary(int $storeId, AnalyticsDateRangeDTO $range): array
    {
        $saleItemType = $this->morphType(ProductSaleItem::class);
        $addonType = $this->morphType(ProductAddon::class);

        $row = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $storeId)
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->selectRaw('COALESCE(SUM(order_items.subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount), 0) as product_discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.coupon_discount_amount), 0) as coupon_discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount + order_items.coupon_discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) as net_sales')
            ->selectRaw('COUNT(DISTINCT orders.id) as paid_orders_count')
            ->selectRaw('COUNT(DISTINCT orders.user_id) as unique_customers')
            ->selectRaw('COUNT(DISTINCT order_items.product_id) as products_sold_count')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as product_units_sold',
                [$saleItemType],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as addons_sold',
                [$addonType],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.final_total_amount ELSE 0 END), 0) as sale_item_net_sales',
                [$saleItemType],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.final_total_amount ELSE 0 END), 0) as addon_net_sales',
                [$addonType],
            )
            ->first();

        $paidOrdersCount = (int)($row->paid_orders_count ?? 0);
        $netSales = (int)($row->net_sales ?? 0);

        return [
            'gross_sales' => (int)($row->gross_sales ?? 0),
            'product_discount_amount' => (int)($row->product_discount_amount ?? 0),
            'coupon_discount_amount' => (int)($row->coupon_discount_amount ?? 0),
            'discount_amount' => (int)($row->discount_amount ?? 0),
            'net_sales' => $netSales,
            'paid_orders_count' => $paidOrdersCount,
            'average_order_value' => $paidOrdersCount > 0
                ? (int)round($netSales / $paidOrdersCount)
                : 0,
            'unique_customers' => (int)($row->unique_customers ?? 0),
            'products_sold_count' => (int)($row->products_sold_count ?? 0),
            'product_units_sold' => (int)($row->product_units_sold ?? 0),
            'addons_sold' => (int)($row->addons_sold ?? 0),
            'sale_item_net_sales' => (int)($row->sale_item_net_sales ?? 0),
            'addon_net_sales' => (int)($row->addon_net_sales ?? 0),
        ];
    }

    /**
     * @param array<string, int|float> $current
     * @param array<string, int|float>|null $previous
     * @return array<string, AnalyticsMetricDTO>
     */
    private function buildMetrics(array $current, ?array $previous): array
    {
        return collect($current)
            ->mapWithKeys(function (int|float $value, string $key) use ($previous): array {
                return [
                    $key => AnalyticsMetricDTO::compare(
                        current: $value,
                        previous: $previous[$key] ?? null,
                    ),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function productPerformance(
        int $storeId,
        AnalyticsDateRangeDTO $range,
        int $totalNetSales,
    ): Collection {
        $saleItemType = $this->morphType(ProductSaleItem::class);
        $addonType = $this->morphType(ProductAddon::class);

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $storeId)
            ->whereNotNull('order_items.product_id')
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->groupBy('order_items.product_id')
            ->select('order_items.product_id')
            ->selectRaw('MAX(order_items.id) as latest_order_item_id')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as sold_quantity',
                [$saleItemType],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) as addons_sold',
                [$addonType],
            )
            ->selectRaw('COALESCE(SUM(order_items.subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount + order_items.coupon_discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) as net_sales')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('COUNT(DISTINCT orders.user_id) as unique_customers')
            ->orderByDesc('net_sales')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $productIds = $rows
            ->pluck('product_id')
            ->map(static fn(mixed $id): int => (int)$id)
            ->all();

        $products = Product::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->whereIn('id', $productIds)
            ->get(['id', 'store_id', 'title', 'slug', 'sku', 'deleted_at'])
            ->keyBy('id');

        $snapshots = DB::table('order_items')
            ->whereIn('id', $rows->pluck('latest_order_item_id'))
            ->get(['id', 'snapshot'])
            ->mapWithKeys(function (object $item): array {
                $snapshot = is_string($item->snapshot)
                    ? json_decode($item->snapshot, true)
                    : (array)$item->snapshot;

                return [(int)$item->id => $snapshot];
            });

        return $rows->map(function (object $row) use (
            $storeId,
            $products,
            $snapshots,
            $totalNetSales,
        ): array {
            $productId = (int)$row->product_id;
            $product = $products->get($productId);
            $snapshot = $snapshots->get((int)$row->latest_order_item_id, []);
            $netSales = (int)$row->net_sales;

            return [
                'product_id' => $productId,
                'title' => $product?->title
                    ?: data_get($snapshot, 'product_title')
                    ?: data_get($snapshot, 'title')
                    ?: 'محصول حذف شده',
                'slug' => $product?->slug,
                'sku' => $product?->sku
                    ?: data_get($snapshot, 'sku'),
                'is_deleted' => $product === null || $product->trashed(),
                'is_moved' => $product !== null && (int)$product->store_id !== $storeId,
                'sold_quantity' => (int)$row->sold_quantity,
                'addons_sold' => (int)$row->addons_sold,
                'orders_count' => (int)$row->orders_count,
                'unique_customers' => (int)$row->unique_customers,
                'gross_sales' => (int)$row->gross_sales,
                'discount_amount' => (int)$row->discount_amount,
                'net_sales' => $netSales,
                'share_percentage' => $totalNetSales > 0
                    ? round(($netSales / $totalNetSales) * 100, 2)
                    : 0.0,
            ];
        })->values();
    }

    private function morphType(string $class): string
    {
        return (string)Relation::getMorphAlias($class);
    }
}
