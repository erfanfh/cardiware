<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\DB;

class GetSellerProductSalesAction
{
    public function execute(Store $store, Product $product, string $period): array
    {
        $saleItemType = (new ProductSaleItem())->getMorphClass();
        $addonType = (new ProductAddon())->getMorphClass();
        $start = $period === 'all' ? null : now()->startOfDay()->subDays((int) $period - 1);
        $end = now()->endOfDay();

        $base = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $store->getKey())
            ->where('order_items.product_id', $product->getKey())
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->when($start, fn ($query) => $query->whereBetween('orders.paid_at', [$start, $end]));

        $row = (clone $base)
            ->selectRaw('COUNT(DISTINCT orders.id) AS orders_count')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) AS net_amount')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->selectRaw('COALESCE(SUM(COALESCE(order_items.platform_commission_amount, order_items.final_total_amount)), 0) AS platform_amount')
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) AS product_units', [$saleItemType])
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END), 0) AS addon_units', [$addonType])
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.final_total_amount ELSE 0 END), 0) AS product_net_amount', [$saleItemType])
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.final_total_amount ELSE 0 END), 0) AS addon_net_amount', [$addonType])
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.seller_commission_amount ELSE 0 END), 0) AS product_seller_amount', [$saleItemType])
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.seller_commission_amount ELSE 0 END), 0) AS addon_seller_amount', [$addonType])
            ->first();

        $summary = [];
        foreach (['orders_count', 'net_amount', 'seller_amount', 'platform_amount', 'product_units', 'addon_units', 'product_net_amount', 'addon_net_amount', 'product_seller_amount', 'addon_seller_amount'] as $field) {
            $summary[$field] = (int) ($row->{$field} ?? 0);
        }

        $groups = (clone $base)
            ->select('order_items.purchasable_type', 'order_items.purchasable_id')
            ->selectRaw('MAX(order_items.id) AS latest_order_item_id')
            ->selectRaw('COUNT(DISTINCT orders.id) AS orders_count')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) AS units')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) AS net_amount')
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->selectRaw('COALESCE(SUM(COALESCE(order_items.platform_commission_amount, order_items.final_total_amount)), 0) AS platform_amount')
            ->groupBy('order_items.purchasable_type', 'order_items.purchasable_id')
            ->orderByDesc('net_amount')
            ->get();

        $saleItems = ProductSaleItem::withTrashed()
            ->where('product_id', $product->getKey())
            ->whereIn('id', $groups->where('purchasable_type', $saleItemType)->pluck('purchasable_id'))
            ->get()
            ->keyBy('id');
        $addons = ProductAddon::withTrashed()
            ->where('product_id', $product->getKey())
            ->whereIn('id', $groups->where('purchasable_type', $addonType)->pluck('purchasable_id'))
            ->get()
            ->keyBy('id');
        $snapshots = DB::table('order_items')
            ->whereIn('id', $groups->pluck('latest_order_item_id'))
            ->pluck('snapshot', 'id');

        $breakdown = $groups->map(function ($group) use ($saleItemType, $addonType, $saleItems, $addons, $snapshots): array {
            $type = (string) $group->purchasable_type;
            $id = (int) $group->purchasable_id;
            $snapshot = $this->decodeSnapshot($snapshots[$group->latest_order_item_id] ?? null);
            $fallback = $type === $saleItemType
                ? $saleItems->get($id)?->title
                : ($type === $addonType ? $addons->get($id)?->title : null);

            return [
                'type' => $type,
                'id' => $id,
                'title' => $this->itemTitle($type, $id, $snapshot, $fallback, $saleItemType, $addonType),
                'orders_count' => (int) $group->orders_count,
                'units' => (int) $group->units,
                'net_amount' => (int) $group->net_amount,
                'seller_amount' => (int) $group->seller_amount,
                'platform_amount' => (int) $group->platform_amount,
                'effective_seller_percent' => (int) $group->net_amount > 0
                    ? round(100 * (int) $group->seller_amount / (int) $group->net_amount, 2)
                    : 0,
            ];
        });

        $recentItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.*')
            ->with('order:id,order_number,paid_at')
            ->where('order_items.store_id', $store->getKey())
            ->where('order_items.product_id', $product->getKey())
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->when($start, fn ($query) => $query->whereBetween('orders.paid_at', [$start, $end]))
            ->orderByDesc('orders.paid_at')
            ->orderByDesc('order_items.id')
            ->paginate(20)
            ->withQueryString();

        $groupTitles = $breakdown->mapWithKeys(fn (array $item) => [$item['type'].':'.$item['id'] => $item['title']]);
        $recentItems->getCollection()->each(function (OrderItem $item) use ($groupTitles, $saleItemType, $addonType): void {
            $item->setAttribute('report_title', $this->itemTitle(
                $item->purchasable_type,
                (int) $item->purchasable_id,
                $item->snapshot ?? [],
                $groupTitles[$item->purchasable_type.':'.$item->purchasable_id] ?? null,
                $saleItemType,
                $addonType,
            ));
        });

        return [
            'period' => $period,
            'summary' => $summary,
            'sale_items' => $breakdown->where('type', $saleItemType)->values(),
            'addons' => $breakdown->where('type', $addonType)->values(),
            'other_items' => $breakdown->reject(fn (array $item) => in_array($item['type'], [$saleItemType, $addonType], true))->values(),
            'recent_items' => $recentItems,
            'sale_item_type' => $saleItemType,
            'addon_type' => $addonType,
        ];
    }

    private function decodeSnapshot(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function itemTitle(string $type, int $id, array $snapshot, ?string $fallback, string $saleItemType, string $addonType): string
    {
        $title = match ($type) {
            $saleItemType => data_get($snapshot, 'product_sale_item_title') ?: data_get($snapshot, 'title') ?: $fallback,
            $addonType => data_get($snapshot, 'product_addon_title') ?: data_get($snapshot, 'title') ?: $fallback,
            default => data_get($snapshot, 'title') ?: $fallback,
        };

        return (string) ($title ?: match ($type) {
            $saleItemType => 'نسخهٔ اصلی محصول',
            $addonType => 'افزونهٔ حذف‌شده #'.$id,
            default => 'آیتم #'.$id,
        });
    }
}
