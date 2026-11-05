<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Actions\RevokePurchaseGamificationForOrderAction;
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

            // پاداش مأموریت خرید هفته و نشان‌های خرید که به این سفارش وابسته بودند.
            app(RevokePurchaseGamificationForOrderAction::class)->execute($order);

            // امتیاز همکار فروش (فروش با لینک/کد او) و پاداش معرفی همین سفارش.
            $this->reverseReferralPoints($order, $event);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function reverseReferralPoints(Order $order, OrderPaymentRevokedEvent $event): void
    {
        $accountIds = \App\Domain\Loyalty\Models\PointLedgerEntry::query()
            ->where('type', 'earn')
            ->whereIn('reason', ['referral_sale', 'referral_bonus'])
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->getKey())
            ->distinct()
            ->pluck('point_account_id');

        $occurrence = $event->occurredAt->utc()->format('YmdHis.u');
        foreach (\App\Domain\Loyalty\Models\PointAccount::query()->whereIn('id', $accountIds)->with('user')->get() as $account) {
            if ($account->user === null) {
                continue;
            }
            foreach (['referral_sale', 'referral_bonus'] as $reason) {
                $this->reverseEarnedPointsForReferenceAction->execute(
                    user: $account->user,
                    reference: $order,
                    reason: $reason,
                    eventKey: "loyalty:reverse:{$reason}:order:{$order->getKey()}:{$occurrence}",
                    occurredAt: $event->occurredAt,
                    metadata: ['event_type' => 'order_payment_revoked', 'order_number' => $order->order_number],
                    description: "برگشت امتیاز همکاری فروش سفارش {$order->order_number}",
                );
            }
        }
    }
}
