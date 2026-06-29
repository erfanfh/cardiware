<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;

class ChangeOrderStatusAction
{
    public function execute(Order $order, OrderStatusEnum $newStatus): Order
    {
        if ($order->status === $newStatus) {
            return $order->refresh();
        }

        $order->forceFill([
            'status' => $newStatus,
            'paid_at' => $newStatus->isPaid()
                ? ($order->paid_at ?? now())
                : $order->paid_at,
            'cancelled_at' => $newStatus === OrderStatusEnum::CANCELLED
                ? ($order->cancelled_at ?? now())
                : $order->cancelled_at,
        ])->save();

        return $order->refresh();
    }
}
