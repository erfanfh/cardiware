<?php

namespace App\Application\Commerce\Support;

use App\Application\Commerce\Contracts\ProductReportablePurchasable;
use App\Application\Commerce\Contracts\Purchasable;

class OrderItemReportDataFactory
{
    public function make(Purchasable $purchasable): array
    {
        if (!$purchasable instanceof ProductReportablePurchasable) {
            return [
                'product_id' => null,
                'store_id' => null,
            ];
        }

        return [
            'product_id' => $purchasable->getReportProductId(),
            'store_id' => $purchasable->getReportStoreId(),
        ];
    }
}
