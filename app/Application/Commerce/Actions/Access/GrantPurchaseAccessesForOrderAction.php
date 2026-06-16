<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class GrantPurchaseAccessesForOrderAction
{
    public function __construct(
        private readonly GrantPurchaseAccessForOrderItemAction $grantPurchaseAccessForOrderItemAction,
    )
    {
    }

    /**
     * Grant purchase accesses for paid order items.
     *
     * @return Collection<int, PurchaseAccess>
     * @throws Throwable
     */
    public function execute(Order $order): Collection
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$order->isPaid()) {
                return collect();
            }

            $order->loadMissing([
                'items.purchasable',
                'items.children.purchasable',
            ]);

            $accesses = collect();

            foreach ($order->items as $orderItem) {
                if ($orderItem->isChild()) {
                    continue;
                }

                $access = $this->grantPurchaseAccessForOrderItemAction->execute(
                    order: $order,
                    orderItem: $orderItem,
                );

                if ($access instanceof PurchaseAccess) {
                    $accesses->push($access);
                }
            }

            return $accesses;
        });
    }
}
