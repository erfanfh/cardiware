<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Commerce\Actions\Access\GrantPurchaseAccessesForOrderAction;
use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Promotion\Actions\Coupon\RedeemCouponForOrderAction;
use App\Domain\Commerce\Models\Order;
use Throwable;

class CompleteOrderAfterSuccessfulPaymentAction
{
    public function __construct(
        private readonly MarkOrderAsPaidAction               $markOrderAsPaidAction,
        private readonly GrantPurchaseAccessesForOrderAction $grantPurchaseAccessesForOrderAction,
        private readonly RedeemCouponForOrderAction          $redeemCouponForOrderAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Order $order): Order
    {
        if (!$order->isPaid()) {
            $order = $this->markOrderAsPaidAction->execute($order);
        }

        $this->redeemCouponForOrderAction->execute($order);

        $this->grantPurchaseAccessesForOrderAction->execute($order);

        return $order->refresh();
    }
}
