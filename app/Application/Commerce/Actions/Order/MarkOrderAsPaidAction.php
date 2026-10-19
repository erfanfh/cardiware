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
    public function __construct(
        private readonly SnapshotOrderItemCommissionsAction $snapshotCommissions,
    ) {}

    /** @throws Throwable */
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === OrderStatusEnum::PAID) {
                return $order;
            }

            if (! $order->status->canBePaid()) {
                throw new DomainException('Order cannot be marked as paid.');
            }

            $transitionedAt = now(config('app.timezone'))->toImmutable();

            $order->forceFill([
                'status' => OrderStatusEnum::PAID,
                'paid_at' => $transitionedAt,
                'metadata' => array_merge($order->metadata ?? [], [
                    'purchase_notification_eligible' => (int) $order->total_amount > 0,
                ]),
            ])->save();

            $this->snapshotCommissions->execute($order);

            $orderId = (int) $order->getKey();

            DB::afterCommit(
                fn () => event(new OrderPaidEvent(
                    orderId: $orderId,
                    occurredAt: $transitionedAt,
                ))
            );

            return $order->refresh();
        });
    }
}
