<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Financial\Actions\Payment\CreateFreePaymentForOrderAction;
use App\Application\Financial\Actions\Payment\MarkPaymentAsPaidAction;
use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompleteFreeOrderAction
{
    public function __construct(
        private readonly CreateFreePaymentForOrderAction           $createFreePaymentForOrderAction,
        private readonly MarkPaymentAsPaidAction                   $markPaymentAsPaidAction,
        private readonly CompleteOrderAfterSuccessfulPaymentAction $completeOrderAfterSuccessfulPaymentAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Order $order): Payment
    {
        $payment = DB::transaction(function () use ($order): Payment {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int)$order->total_amount > 0) {
                throw new DomainException('Only free orders can be completed without gateway payment.');
            }

            $payment = $this->createFreePaymentForOrderAction->execute($order);

            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$payment->isPaid()) {
                $payment = $this->markPaymentAsPaidAction->execute(
                    payment: $payment,
                    gatewayTransactionId: null,
                    gatewayStatus: 'free',
                    gatewayMessage: 'سفارش رایگان بدون اتصال به درگاه پرداخت تکمیل شد.',
                    verifyPayload: [
                        'type' => 'free_order',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'verified_without_gateway' => true,
                    ],
                );
            }

            $this->completeOrderAfterSuccessfulPaymentAction->execute($order);

            return $payment->refresh();
        });

        $payment->loadMissing('payable.user');

        $user = $payment->payable?->user;

        if ($user) {
            app(UserCacheInvalidator::class)->flushForUser($user);
            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();
        }

        return $payment;
    }
}
