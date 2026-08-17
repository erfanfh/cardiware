<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateOrderStatusAction
{
    public function __construct(
        private readonly OrderStatusTransitionGuard               $orderStatusTransitionGuard,
        private readonly ChangeOrderStatusAction                  $changeOrderStatusAction,
        private readonly SyncPurchaseAccessesForOrderStatusAction $syncPurchaseAccessesForOrderStatusAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Order $order, OrderStatusEnum $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus): Order {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $order->status;

            if ($oldStatus === $newStatus) {
                return $order->refresh();
            }

            $this->orderStatusTransitionGuard->ensureAllowed(
                oldStatus: $oldStatus,
                newStatus: $newStatus,
            );

            $transitionedAt = now(config('app.timezone'))->toImmutable();

            $order = $this->changeOrderStatusAction->execute(
                order: $order,
                newStatus: $newStatus,
                transitionedAt: $transitionedAt,
            );

            $this->syncPurchaseAccessesForOrderStatusAction->execute(
                order: $order,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
            );

            app(UserCacheInvalidator::class)->flushForUser($order->user);
            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();

            $orderId = (int)$order->getKey();

            if (!$oldStatus->isPaid() && $newStatus->isPaid()) {
                DB::afterCommit(
                    fn() => event(
                        new OrderPaidEvent(
                            orderId: $orderId,
                            occurredAt: $transitionedAt,
                        )
                    )
                );
            } elseif ($oldStatus->isPaid() && !$newStatus->isPaid()) {
                DB::afterCommit(
                    fn() => event(
                        new OrderPaymentRevokedEvent(
                            orderId: $orderId,
                            reason: $newStatus->value,
                            occurredAt: $transitionedAt,
                        )
                    )
                );
            }

            return $order->refresh();
        });
    }
}
