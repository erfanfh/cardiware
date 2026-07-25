<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateOrderDescriptionAction
{
    /**
     * @throws Throwable
     */
    public function execute(Order $order, ?string $description): Order
    {
        return DB::transaction(function () use ($order, $description): Order {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $order->update(['description' => $description ?: null]);

            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();

            return $order->refresh();
        });
    }
}
