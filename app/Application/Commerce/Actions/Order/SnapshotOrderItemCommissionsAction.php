<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Facades\DB;

class SnapshotOrderItemCommissionsAction
{
    /**
     * Called inside the order payment transaction. Existing snapshots are never replaced.
     */
    public function execute(Order $order): void
    {
        $items = $order->items()->whereNull('commission_snapshotted_at')->get();

        if ($items->isEmpty()) {
            return;
        }

        $rates = DB::table('products')
            ->whereIn('id', $items->pluck('product_id')->filter()->unique())
            ->pluck('seller_commission_basis_points', 'id');

        $addonMorphType = (new ProductAddon())->getMorphClass();
        $addonRates = DB::table('product_addons')
            ->whereIn('id', $items
                ->filter(fn ($item) => $item->purchasable_type === $addonMorphType)
                ->pluck('purchasable_id')
                ->unique())
            ->get(['id', 'product_id', 'seller_commission_basis_points'])
            ->keyBy('id');

        foreach ($items as $item) {
            $sellerRate = $item->product_id !== null
                ? min(10000, max(0, (int) ($rates[$item->product_id] ?? 0)))
                : 0;

            if ($item->purchasable_type === $addonMorphType) {
                $addon = $addonRates[$item->purchasable_id] ?? null;
                if ($addon !== null
                    && (int) $addon->product_id === (int) $item->product_id
                    && $addon->seller_commission_basis_points !== null) {
                    $sellerRate = min(10000, max(0, (int) $addon->seller_commission_basis_points));
                }
            }
            $platformRate = 10000 - $sellerRate;
            $amount = max(0, (int) $item->final_total_amount);
            $sellerAmount = intdiv($amount, 10000) * $sellerRate
                + intdiv(($amount % 10000) * $sellerRate + 5000, 10000);

            $item->forceFill([
                'seller_commission_basis_points' => $sellerRate,
                'platform_commission_basis_points' => $platformRate,
                'seller_commission_amount' => $sellerAmount,
                'platform_commission_amount' => $amount - $sellerAmount,
                'commission_snapshotted_at' => now(),
            ])->save();
        }
    }
}
