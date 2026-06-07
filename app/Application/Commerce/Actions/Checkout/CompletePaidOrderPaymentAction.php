<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Commerce\Actions\Access\GrantPurchaseAccessesForOrderAction;
use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompletePaidOrderPaymentAction
{
    public function __construct(
        private readonly MarkOrderAsPaidAction               $markOrderAsPaidAction,
        private readonly GrantPurchaseAccessesForOrderAction $grantPurchaseAccessesForOrderAction,
    )
    {
    }

    /**
     * Complete commerce side effects after a successful order payment.
     *
     * @param Payment $payment
     * @return Order
     * @throws Throwable
     */
    public function execute(Payment $payment): Order
    {
        return DB::transaction(function () use ($payment) {
            $payment = $payment->refresh();

            if (!$payment->isPaid()) {
                throw new DomainException('Only paid payments can complete an order.');
            }

            $payable = $payment->payable;

            if (!$payable instanceof Order) {
                throw new DomainException('Payment payable is not an order.');
            }

            $order = $this->markOrderAsPaidAction->execute($payable);

            $this->grantPurchaseAccessesForOrderAction->execute($order);

            return $order->refresh();
        });
    }
}
