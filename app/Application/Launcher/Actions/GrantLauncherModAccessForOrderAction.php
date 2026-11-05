<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Commerce\Models\Order;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModGrant;
use Illuminate\Support\Facades\DB;

/**
 * پس از پرداخت سفارش، برای همه‌ی اقلامِ مادِ لانچر یک گرانت دائمی
 * (source=purchase) ثبت می‌کند. خرید تکی ماد همیشه دائمی است؛ حالت
 * موقت فقط برای مادهای vip با unlock_mode=subscription وجود دارد که
 * از مسیر گرانت عبور نمی‌کنند و زنده از روی اشتراک چک می‌شوند.
 */
class GrantLauncherModAccessForOrderAction
{
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->isPaid()) {
                return;
            }

            $items = $order->items()
                ->where('purchasable_type', LauncherMod::MORPH_ALIAS)
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            foreach ($items as $item) {
                $mod = LauncherMod::query()->find($item->purchasable_id);

                // پرداخت انجام شده؛ حتی اگر ادمین در این فاصله نوع دسترسی ماد
                // را عوض کرده باشد، خریدار باید دسترسی‌اش را بگیرد.
                if (! $mod instanceof LauncherMod) {
                    continue;
                }

                LauncherModGrant::query()->firstOrCreate(
                    [
                        'user_id' => $order->user_id,
                        'launcher_mod_id' => $mod->getKey(),
                    ],
                    [
                        'source' => LauncherModGrant::SOURCE_PURCHASE,
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                    ],
                );
            }
        }, 3);
    }
}
