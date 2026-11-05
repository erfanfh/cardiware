<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Commerce\Models\Order;
use App\Domain\Launcher\Models\LauncherModGrant;

/**
 * وقتی سفارش پرداخت‌شده برگشت/لغو می‌شود، گرانت‌های خرید تکی ماد که از همان
 * سفارش آمده‌اند حذف می‌شوند تا ماد دوباره قفل شود. گرانت‌های vip_permanent
 * به سفارش وابسته نیستند و دست نمی‌خورند.
 */
class RevokeLauncherModAccessForOrderAction
{
    public function execute(Order $order): void
    {
        if (! $order->items()->where('purchasable_type', \App\Domain\Launcher\Models\LauncherMod::MORPH_ALIAS)->exists()) {
            return;
        }

        LauncherModGrant::query()
            ->where('order_id', $order->getKey())
            ->where('source', LauncherModGrant::SOURCE_PURCHASE)
            ->delete();
    }
}
