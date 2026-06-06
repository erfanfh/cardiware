<?php

namespace App\Application\Commerce\Events;

use App\Domain\Commerce\Models\Order;

class OrderPaid
{
    public function __construct(
        public readonly Order $order,
    )
    {
    }
}
