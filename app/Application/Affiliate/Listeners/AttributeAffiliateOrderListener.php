<?php

namespace App\Application\Affiliate\Listeners;

use App\Application\Affiliate\Actions\AttributeAffiliateOrderAction;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use Throwable;

/**
 * ثبت کمیسیون همکاری در فروش پس از پرداخت موفق سفارش.
 * هیچ‌وقت نباید پرداخت کامیت‌شده را شکست دهد.
 */
class AttributeAffiliateOrderListener
{
    public function handle(OrderPaidEvent $event): void
    {
        try {
            $order = Order::query()->with('user')->find($event->orderId);

            if ($order?->user === null) {
                return;
            }

            app(AttributeAffiliateOrderAction::class)->execute($order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
