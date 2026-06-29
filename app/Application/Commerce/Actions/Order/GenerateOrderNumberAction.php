<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Order;

class GenerateOrderNumberAction
{
    /**
     * Generate a unique order number.
     *
     * @param Order $order
     * @return string
     */
    public function execute(Order $order): string
    {
        return (string)($order->id + 1000);
    }
}
