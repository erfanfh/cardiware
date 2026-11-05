<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Launcher\Models\LauncherMod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * سفارش‌های پرداخت‌شده‌ی همان کاربر که ماد پولیِ محصولاتِ این سفارش را
 * خریده‌اند. وقتی ادمین سفارش محصول را مرجوع می‌کند، این مادها دیگر به
 * کاری نمی‌آیند و می‌تواند همراه آن (با مبلغ دلخواه) مرجوعشان کند.
 */
class FindRelatedLauncherModOrdersAction
{
    /** @return Collection<int, Order> */
    public function execute(Order $order): Collection
    {
        $saleItemIds = $order->items()
            ->where('purchasable_type', (new ProductSaleItem())->getMorphClass())
            ->pluck('purchasable_id')
            ->map(static fn($id): int => (int) $id)
            ->unique()
            ->values();

        if ($saleItemIds->isEmpty()) {
            return new Collection();
        }

        $modIds = LauncherMod::query()
            ->whereIn('product_sale_item_id', $saleItemIds)
            ->pluck('id');

        if ($modIds->isEmpty()) {
            return new Collection();
        }

        return Order::query()
            ->where('user_id', $order->user_id)
            ->whereKeyNot($order->getKey())
            ->where('status', OrderStatusEnum::PAID->value)
            ->whereHas('items', fn(Builder $query) => $query
                ->where('purchasable_type', LauncherMod::MORPH_ALIAS)
                ->whereIn('purchasable_id', $modIds))
            ->with('items.purchasable')
            ->orderBy('id')
            ->get();
    }
}
