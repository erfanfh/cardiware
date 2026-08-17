<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Actions\ReverseEarnedPointsForReferenceAction;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Throwable;

class ReversePurchasePointsListener
{
    public function __construct(
        private readonly ReverseEarnedPointsForReferenceAction $reverseEarnedPointsForReferenceAction,
    ) {
    }

    public function handle(OrderPaymentRevokedEvent $event): void
    {
        try {
            $order = Order::query()->with('user')->find($event->orderId);
            if (!$order?->user) {
                return;
            }

            $occurrence = $event->occurredAt->utc()->format('YmdHis.u');

            $this->reverseEarnedPointsForReferenceAction->execute(
                user: $order->user,
                reference: $order,
                reason: EarningRuleTypeEnum::PURCHASE->value,
                eventKey: "loyalty:reverse:purchase:order:{$order->getKey()}:{$occurrence}",
                occurredAt: $event->occurredAt,
                metadata: [
                    'event_type' => 'order_payment_revoked',
                    'revocation_reason' => $event->reason,
                    'order_number' => $order->order_number,
                ],
                description: "برگشت امتیاز خرید سفارش {$order->order_number}",
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
