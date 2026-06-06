<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Events\OrderPaid;
use App\Domain\Commerce\Enums\OrderStatusEnum;
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
            $order = $order->refresh();

            if ($order->status === OrderStatusEnum::PAID) {
                return $order;
            }

            if (!$order->status->canBePaid()) {
                throw new DomainException('Order cannot be marked as paid.');
            }

            $order->forceFill([
                'status' => OrderStatusEnum::PAID,
                'paid_at' => now(),
            ])->save();

            $order = $order->refresh();

            event(new OrderPaid($order));

            return $order;
        });
    }
}
