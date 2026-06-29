<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Enums\OrderStatusEnum;
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

            $order = $this->changeOrderStatusAction->execute(
                order: $order,
                newStatus: $newStatus,
            );

            $this->syncPurchaseAccessesForOrderStatusAction->execute(
                order: $order,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
            );

            app(UserCacheInvalidator::class)->flushForUser($order->user);
            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();

            return $order->refresh();
        });
    }
}
