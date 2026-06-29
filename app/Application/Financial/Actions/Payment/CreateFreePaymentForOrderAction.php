<?php

namespace App\Application\Financial\Actions\Payment;

use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;

class CreateFreePaymentForOrderAction
{
    public function __construct(
        private readonly GeneratePaymentTrackingCodeAction $generatePaymentTrackingCodeAction,
    )
    {
    }

    public function execute(Order $order): Payment
    {
        if ((int)$order->total_amount > 0) {
            throw new DomainException('Only free orders can have free payment.');
        }

        $existingPayment = Payment::query()
            ->where('payable_type', $order->getMorphClass())
            ->where('payable_id', $order->getKey())
            ->where('gateway', PaymentGatewayEnum::FREE)
            ->first();

        if ($existingPayment) {
            return $existingPayment->refresh();
        }

        return Payment::query()->create([
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->getKey(),
            'gateway' => PaymentGatewayEnum::FREE,
            'status' => PaymentStatusEnum::INITIATED,
            'amount' => 0,
            'tracking_code' => $this->generatePaymentTrackingCodeAction->execute(),

            'gateway_reference' => 'FREE-' . $order->order_number,
            'gateway_message' => 'سفارش رایگان بدون اتصال به درگاه پرداخت ایجاد شد.',

            'request_payload' => [
                'type' => 'free_order',
                'source' => 'web',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }
}
