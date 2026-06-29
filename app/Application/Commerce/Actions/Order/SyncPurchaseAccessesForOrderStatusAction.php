<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Actions\Access\GrantPurchaseAccessesForOrderAction;
use App\Application\Commerce\Actions\Access\RevokePurchaseAccessesForOrderAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use Throwable;

class SyncPurchaseAccessesForOrderStatusAction
{
    public function __construct(
        private readonly GrantPurchaseAccessesForOrderAction  $grantPurchaseAccessesForOrderAction,
        private readonly RevokePurchaseAccessesForOrderAction $revokePurchaseAccessesForOrderAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(
        Order           $order,
        OrderStatusEnum $oldStatus,
        OrderStatusEnum $newStatus,
    ): void
    {
        $wasGrantingAccess = $oldStatus->grantsPurchaseAccess();
        $shouldGrantAccess = $newStatus->grantsPurchaseAccess();

        if (!$wasGrantingAccess && $shouldGrantAccess) {
            $this->grantPurchaseAccessesForOrderAction->execute($order);
            return;
        }

        if ($wasGrantingAccess && !$shouldGrantAccess) {
            $this->revokePurchaseAccessesForOrderAction->execute(
                order: $order,
                reason: 'order_status_changed_from_' . $oldStatus->value . '_to_' . $newStatus->value,
            );
        }
    }
}
