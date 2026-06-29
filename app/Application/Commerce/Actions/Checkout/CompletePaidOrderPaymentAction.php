<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompletePaidOrderPaymentAction
{
    public function __construct(
        private readonly CompleteOrderAfterSuccessfulPaymentAction $completeOrderAfterSuccessfulPaymentAction,
    )
    {
    }

    /**
     * Complete commerce side effects after a successful order payment.
     *
     * @throws Throwable
     */
    public function execute(Payment $payment): Order
    {
        $order = DB::transaction(function () use ($payment): Order {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$payment->isPaid()) {
                throw new DomainException('Only paid payments can complete an order.');
            }

            $payable = $payment->payable;

            if (!$payable instanceof Order) {
                throw new DomainException('Payment payable is not an order.');
            }

            /** @var Order $order */
            $order = Order::query()
                ->whereKey($payable->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->completeOrderAfterSuccessfulPaymentAction->execute($order);
        });

        $order->loadMissing('user');

        if ($order->user) {
            app(UserCacheInvalidator::class)->flushForUser($order->user);
            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();
        }

        return $order;
    }
}
