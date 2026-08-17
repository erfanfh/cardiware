<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\PurchaseEarningContext;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Throwable;

class AwardPurchasePointsListener
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
    ) {
    }

    public function handle(OrderPaidEvent $event): void
    {
        try {
            $order = Order::query()->with('user')->find($event->orderId);
            if (!$order?->user) {
                return;
            }

            $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::PURCHASE,
                new PurchaseEarningContext(
                    user: $order->user,
                    order: $order,
                    occurredAt: $event->occurredAt,
                ),
            );
        } catch (Throwable $exception) {
            // Loyalty must never make a committed payment fail. The scheduled
            // reconciliation command replays the current durable order state.
            report($exception);
        }
    }
}
