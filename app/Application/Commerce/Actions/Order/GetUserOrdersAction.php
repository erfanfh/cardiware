<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Collection;

class GetUserOrdersAction
{
    /**
     * Get latest user orders.
     *
     * @return Collection<int, Order>
     */
    public function execute(int $userId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with([
                'rootItems.children',
                'rootItems.purchasable',
                'latestPayment',
                'payments',
                'purchaseAccesses',
            ])
            ->latest('id')
            ->get();
    }
}
