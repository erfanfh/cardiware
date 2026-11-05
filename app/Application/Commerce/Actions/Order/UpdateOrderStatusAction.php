<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Application\Launcher\Actions\GrantLauncherModAccessForOrderAction;
use App\Application\Launcher\Actions\RevokeLauncherModAccessForOrderAction;
use App\Application\Vip\Actions\GrantVipSubscriptionsForOrderAction;
use App\Application\Vip\Actions\RevokeVipSubscriptionsForOrderAction;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateOrderStatusAction
{
    public function __construct(
        private readonly OrderStatusTransitionGuard               $orderStatusTransitionGuard,
        private readonly ChangeOrderStatusAction                  $changeOrderStatusAction,
        private readonly SyncPurchaseAccessesForOrderStatusAction $syncPurchaseAccessesForOrderStatusAction,
        private readonly RecordOrderRefundAction                   $recordOrderRefundAction,
        private readonly SnapshotOrderItemCommissionsAction        $snapshotCommissions,
        private readonly GrantVipSubscriptionsForOrderAction       $grantVipSubscriptions,
        private readonly RevokeVipSubscriptionsForOrderAction      $revokeVipSubscriptions,
        private readonly GrantLauncherModAccessForOrderAction      $grantLauncherModAccess,
        private readonly RevokeLauncherModAccessForOrderAction     $revokeLauncherModAccess,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(
        Order $order,
        OrderStatusEnum $newStatus,
        ?OrderRefundMethodEnum $refundMethod = null,
        ?User $admin = null,
        ?int $refundAmount = null,
    ): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $refundMethod, $admin, $refundAmount): Order {
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

            if ($oldStatus === OrderStatusEnum::REFUNDED && $order->refund()->exists()) {
                throw new DomainException('Refunded order cannot be marked as paid again.');
            }

            if ($newStatus === OrderStatusEnum::REFUNDED && $oldStatus === OrderStatusEnum::PAID) {
                if ($order->refund()->exists()) {
                    throw new DomainException('This order has already been refunded.');
                }

                if ((int) $order->getPayableAmount() > 0) {
                    if ($refundMethod === null) {
                        throw new DomainException('Refund method is required.');
                    }

                    $this->recordOrderRefundAction->execute(
                        order: $order,
                        method: $refundMethod,
                        admin: $admin,
                        amount: $refundAmount,
                    );
                }
            }

            $transitionedAt = now(config('app.timezone'))->toImmutable();

            $order = $this->changeOrderStatusAction->execute(
                order: $order,
                newStatus: $newStatus,
                transitionedAt: $transitionedAt,
            );

            if (!$oldStatus->isPaid() && $newStatus->isPaid()) {
                $this->snapshotCommissions->execute($order);
            }

            $this->syncPurchaseAccessesForOrderStatusAction->execute(
                order: $order,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
            );

            if (!$oldStatus->isPaid() && $newStatus->isPaid()) {
                $this->grantVipSubscriptions->execute($order);
                $this->grantLauncherModAccess->execute($order);
            } elseif ($oldStatus->isPaid() && !$newStatus->isPaid()) {
                $this->revokeVipSubscriptions->execute($order);
                $this->revokeLauncherModAccess->execute($order);
            }

            app(UserCacheInvalidator::class)->flushForUser((int) $order->user_id);
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
