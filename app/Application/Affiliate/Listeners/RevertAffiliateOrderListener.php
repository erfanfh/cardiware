<?php

namespace App\Application\Affiliate\Listeners;

use App\Application\Affiliate\Actions\RevertAffiliateOrderAction;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use Throwable;

class RevertAffiliateOrderListener
{
    public function handle(OrderPaymentRevokedEvent $event): void
    {
        try {
            $order = Order::query()->find($event->orderId);

            if ($order === null) {
                return;
            }

            app(RevertAffiliateOrderAction::class)->execute($order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
