<?php

namespace App\Application\Commerce\Reports;

use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProductSalesReport
{
    /**
     * گزارش کلی فروش یک محصول
     * شامل فروش آیتم اصلی محصول + افزونه‌ها
     */
    public function summary(
        int     $productId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): array
    {
        $summary = $this->baseQuery($from, $to, $statuses)
            ->where('order_items.product_id', $productId)
            ->selectRaw('
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ')
            ->first();

        return $this->formatSummaryRow($summary, [
            'product_id' => $productId,
        ]);
    }

    /**
     * فقط فروش آیتم اصلی محصول
     * یعنی ProductSaleItem ها
     */
    public function saleItemSummary(
        int     $productId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): array
    {
        $summary = $this->baseQuery($from, $to, $statuses)
            ->where('order_items.product_id', $productId)
            ->where('order_items.purchasable_type', $this->morphType(ProductSaleItem::class))
            ->selectRaw('
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ')
            ->first();

        return $this->formatSummaryRow($summary, [
            'product_id' => $productId,
            'purchasable_type' => $this->morphType(ProductSaleItem::class),
        ]);
    }

    /**
     * فقط فروش افزونه‌های محصول
     * یعنی ProductAddon ها
     */
    public function addonSummary(
        int     $productId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): array
    {
        $summary = $this->baseQuery($from, $to, $statuses)
            ->where('order_items.product_id', $productId)
            ->where('order_items.purchasable_type', $this->morphType(ProductAddon::class))
            ->selectRaw('
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ')
            ->first();

        return $this->formatSummaryRow($summary, [
            'product_id' => $productId,
            'purchasable_type' => $this->morphType(ProductAddon::class),
        ]);
    }

    /**
     * تفکیک فروش محصول بر اساس purchasable_type
     * مثلا:
     * - product_sale_item
     * - product_addon
     */
    public function breakdownByPurchasableType(
        int     $productId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Collection
    {
        return $this->baseQuery($from, $to, $statuses)
            ->where('order_items.product_id', $productId)
            ->groupBy('order_items.purchasable_type')
            ->selectRaw('
                order_items.purchasable_type,
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ')
            ->get()
            ->map(function ($row) use ($productId) {
                return $this->formatSummaryRow($row, [
                    'product_id' => $productId,
                    'purchasable_type' => $row->purchasable_type,
                ]);
            });
    }

    /**
     * query آماده برای لیست محصولات
     * برای leftJoinSub داخل Product query عالیه.
     */
    public function productsSummaryQuery(
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Builder
    {
        return $this->baseQuery($from, $to, $statuses)
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->selectRaw('
                order_items.product_id,
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ');
    }

    /**
     * گزارش کلی همه محصولات
     */
    public function productsSummary(
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Collection
    {
        return $this->productsSummaryQuery($from, $to, $statuses)
            ->get()
            ->map(function ($row) {
                return $this->formatSummaryRow($row, [
                    'product_id' => (int)$row->product_id,
                ]);
            });
    }

    /**
     * query آماده برای لیست محصولات فقط بر اساس فروش آیتم اصلی
     */
    public function productsSaleItemSummaryQuery(
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Builder
    {
        return $this->baseQuery($from, $to, $statuses)
            ->whereNotNull('order_items.product_id')
            ->where('order_items.purchasable_type', $this->morphType(ProductSaleItem::class))
            ->groupBy('order_items.product_id')
            ->selectRaw('
                order_items.product_id,
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ');
    }

    /**
     * query آماده برای لیست محصولات فقط بر اساس فروش addon
     */
    public function productsAddonSummaryQuery(
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Builder
    {
        return $this->baseQuery($from, $to, $statuses)
            ->whereNotNull('order_items.product_id')
            ->where('order_items.purchasable_type', $this->morphType(ProductAddon::class))
            ->groupBy('order_items.product_id')
            ->selectRaw('
                order_items.product_id,
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ');
    }

    /**
     * گزارش فروش یک فروشگاه
     */
    public function storeSummary(
        int     $storeId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): array
    {
        $summary = $this->baseQuery($from, $to, $statuses)
            ->where('order_items.store_id', $storeId)
            ->selectRaw('
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(DISTINCT order_items.product_id) as products_count,
                COUNT(order_items.id) as items_count
            ')
            ->first();

        return $this->formatSummaryRow($summary, [
            'store_id' => $storeId,
            'products_count' => (int)$summary->products_count,
        ]);
    }

    /**
     * گزارش فروش محصولات یک فروشگاه، به تفکیک محصول
     */
    public function storeProductsSummary(
        int     $storeId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Collection
    {
        return $this->baseQuery($from, $to, $statuses)
            ->where('order_items.store_id', $storeId)
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->selectRaw('
                order_items.product_id,
                COALESCE(SUM(order_items.quantity), 0) as sold_quantity,
                COALESCE(SUM(order_items.subtotal_amount), 0) as gross_amount,
                COALESCE(SUM(order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(order_items.total_amount), 0) as net_amount,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(order_items.id) as items_count
            ')
            ->get()
            ->map(function ($row) use ($storeId) {
                return $this->formatSummaryRow($row, [
                    'store_id' => $storeId,
                    'product_id' => (int)$row->product_id,
                ]);
            });
    }

    private function baseQuery(
        ?Carbon $from = null,
        ?Carbon $to = null,
        array   $statuses = [OrderStatusEnum::PAID],
    ): Builder
    {
        $statusValues = collect($statuses)
            ->map(fn($status) => $status instanceof OrderStatusEnum ? $status->value : $status)
            ->values()
            ->all();

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $statusValues)
            ->when($from, function (Builder $query) use ($from) {
                $query->where('orders.created_at', '>=', $from);
            })
            ->when($to, function (Builder $query) use ($to) {
                $query->where('orders.created_at', '<=', $to);
            });
    }

    private function formatSummaryRow(object $row, array $extra = []): array
    {
        return array_merge($extra, [
            'sold_quantity' => (int)$row->sold_quantity,
            'gross_amount' => (int)$row->gross_amount,
            'discount_amount' => (int)$row->discount_amount,
            'net_amount' => (int)$row->net_amount,
            'orders_count' => (int)$row->orders_count,
            'items_count' => (int)$row->items_count,
        ]);
    }

    /**
     * اگر morph map داشته باشی alias برمی‌گردونه.
     * اگر نداشته باشی class کامل برمی‌گردونه.
     */
    private function morphType(string $class): string
    {
        $morphMap = Relation::morphMap() ?: [];

        $alias = array_search($class, $morphMap, true);

        return $alias !== false ? $alias : $class;
    }
}
