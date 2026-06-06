<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Order;
use Random\RandomException;

class GenerateOrderNumberAction
{
    /**
     * Generate a unique order number.
     *
     * @return string
     * @throws RandomException
     */
    public function execute(): string
    {
        do {
            $orderNumber = 'MW-' . now()->format('Ymd') . '-' . str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            Order::query()
                ->where('order_number', $orderNumber)
                ->exists()
        );

        return $orderNumber;
    }
}
