<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class MarkOrderAsPaidAction
{
    /**
     * Mark order as paid.
     *
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === OrderStatusEnum::PAID) {
                return $order;
            }

            if (!$order->status->canBePaid()) {
                throw new DomainException('Order cannot be marked as paid.');
            }

            $order->forceFill([
                'status' => OrderStatusEnum::PAID,
                'paid_at' => $order->paid_at ?? now(),
            ])->save();

            $orderId = (int)$order->getKey();
            $occurredAt = $order->paid_at?->toImmutable()
                ?? now(config('app.timezone'))->toImmutable();

            DB::afterCommit(
                fn() => event(
                    new OrderPaidEvent(
                        orderId: $orderId,
                        occurredAt: $occurredAt,
                    )
                )
            );

            return $order->refresh();
        });
    }
}
