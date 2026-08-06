<?php

namespace App\Application\Commerce\Analytics\Actions;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsMetricDTO;
use App\Application\Commerce\Analytics\DTOs\ProductAnalyticsDTO;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetProductAnalyticsAction
{
    public function __construct(
        private readonly BuildProductSalesTrendAction $buildProductSalesTrendAction,
    ) {
    }

    public function execute(int $productId, AnalyticsFilterDTO $filter): ProductAnalyticsDTO
    {
        return Cache::remember(
            $this->cacheKey($productId, $filter),
            now()->addSeconds($filter->preset->cacheTtlSeconds()),
            fn(): ProductAnalyticsDTO => $this->build($productId, $filter),
        );
    }

    public function forget(int $productId, AnalyticsFilterDTO $filter): void
    {
        Cache::forget($this->cacheKey($productId, $filter));
    }

    private function build(int $productId, AnalyticsFilterDTO $filter): ProductAnalyticsDTO
    {
        $current = $this->summary($productId, $filter->range);
        $previous = $filter->comparisonRange
            ? $this->summary($productId, $filter->comparisonRange)
            : null;

        $currentTrend = $this->buildProductSalesTrendAction->execute(
            productId: $productId,
            range: $filter->range,
            granularity: $filter->granularity,
        );

        $comparisonTrend = $filter->comparisonRange
            ? $this->buildProductSalesTrendAction->execute(
                productId: $productId,
                range: $filter->comparisonRange,
                granularity: $filter->granularity,
            )
            : null;

        return new ProductAnalyticsDTO(
            filter: $filter,
            metrics: $this->buildMetrics($current, $previous),
            salesTrend: $this->buildProductSalesTrendAction->mergeComparison(
                current: $currentTrend,
                comparison: $comparisonTrend,
            ),
            itemPerformance: $this->itemPerformance(
                productId: $productId,
                range: $filter->range,
                totalNetSales: (int)$current['net_sales'],
            ),
            generatedAt: now()->toImmutable(),
        );
    }

    private function cacheKey(int $productId, AnalyticsFilterDTO $filter): string
    {
        return sprintf(
            'commerce-analytics:product:v1:%d:%s',
            $productId,
            $filter->cacheKey(),
        );
    }

    /**
     * @return array<string, int|float>
     */
    private function summary(int $productId, AnalyticsDateRangeDTO $range): array
    {
        $saleItemType = $this->morphType(ProductSaleItem::class);
        $addonType = $this->morphType(ProductAddon::class);

        $row = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
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
    private function itemPerformance(
        int $productId,
        AnalyticsDateRangeDTO $range,
        int $totalNetSales,
    ): Collection {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->groupBy('order_items.purchasable_type', 'order_items.purchasable_id')
            ->select([
                'order_items.purchasable_type',
                'order_items.purchasable_id',
            ])
            ->selectRaw('MAX(order_items.id) as latest_order_item_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity')
            ->selectRaw('COALESCE(SUM(order_items.subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount + order_items.coupon_discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) as net_sales')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->orderByDesc('net_sales')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $saleItemType = $this->morphType(ProductSaleItem::class);
        $addonType = $this->morphType(ProductAddon::class);

        $saleItemIds = $rows
            ->where('purchasable_type', $saleItemType)
            ->pluck('purchasable_id')
            ->map(static fn(mixed $id): int => (int)$id)
            ->all();

        $addonIds = $rows
            ->where('purchasable_type', $addonType)
            ->pluck('purchasable_id')
            ->map(static fn(mixed $id): int => (int)$id)
            ->all();

        $saleItems = ProductSaleItem::query()
            ->withTrashed()
            ->whereIn('id', $saleItemIds)
            ->get()
            ->keyBy('id');

        $addons = ProductAddon::query()
            ->withTrashed()
            ->whereIn('id', $addonIds)
            ->get()
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
            $saleItemType,
            $addonType,
            $saleItems,
            $addons,
            $snapshots,
            $totalNetSales,
        ): array {
            $id = (int)$row->purchasable_id;
            $snapshot = $snapshots->get((int)$row->latest_order_item_id, []);
            $netSales = (int)$row->net_sales;

            if ($row->purchasable_type === $saleItemType) {
                $model = $saleItems->get($id);
                $title = $model?->title
                    ?: data_get($snapshot, 'product_sale_item_title')
                    ?: 'نسخه اصلی محصول';
                $subtitle = $model?->sku
                    ?: data_get($snapshot, 'sku');
                $typeLabel = 'آیتم اصلی';
                $isDeleted = $model === null || $model->trashed();
            } elseif ($row->purchasable_type === $addonType) {
                $model = $addons->get($id);
                $title = $model?->title
                    ?: data_get($snapshot, 'product_addon_title')
                    ?: data_get($snapshot, 'title')
                    ?: 'افزونه حذف شده';
                $subtitle = null;
                $typeLabel = 'افزونه';
                $isDeleted = $model === null || $model->trashed();
            } else {
                $title = data_get($snapshot, 'title')
                    ?: 'آیتم ناشناخته #' . $id;
                $subtitle = null;
                $typeLabel = 'سایر';
                $isDeleted = false;
            }

            return [
                'purchasable_type' => (string)$row->purchasable_type,
                'purchasable_id' => $id,
                'type_label' => $typeLabel,
                'title' => $title,
                'subtitle' => $subtitle,
                'is_deleted' => $isDeleted,
                'sold_quantity' => (int)$row->sold_quantity,
                'orders_count' => (int)$row->orders_count,
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
