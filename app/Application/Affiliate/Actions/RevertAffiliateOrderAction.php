<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\Enums\AffiliateOrderStatusEnum;
use App\Domain\Affiliate\Models\AffiliateOrder;
use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * کلابک کمیسیون در صورت ریفاند/لغو پرداخت سفارش.
 * موجودی قابل برداشت به‌صورت زنده از دفتر محاسبه می‌شود، پس کافی است
 * ردیف کمیسیون به وضعیت reverted برود.
 */
class RevertAffiliateOrderAction
{
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            AffiliateOrder::query()
                ->where('order_id', $order->getKey())
                ->where('status', AffiliateOrderStatusEnum::ACTIVE->value)
                ->lockForUpdate()
                ->get()
                ->each(function (AffiliateOrder $affiliateOrder): void {
                    $affiliateOrder->update(['status' => AffiliateOrderStatusEnum::REVERTED->value]);
                });
        });
    }
}
