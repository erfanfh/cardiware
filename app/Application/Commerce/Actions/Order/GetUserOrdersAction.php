<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserOrdersAction
{
    /**
     * Get latest user orders.
     */
    public function execute(int $userId, int $perPage = 5): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with([
                'rootItems.children',
                'rootItems.purchasable',
                'latestPayment',
                'purchaseAccesses',
            ])
            ->latest('id')
            ->paginate($perPage);
    }
}
