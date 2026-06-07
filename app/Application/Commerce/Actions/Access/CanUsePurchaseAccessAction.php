<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;

class CanUsePurchaseAccessAction
{
    /**
     * Determine whether the purchase access is usable.
     */
    public function execute(PurchaseAccess $purchaseAccess): bool
    {
        $purchaseAccess = $purchaseAccess->refresh();

        return $purchaseAccess->isUsable();
    }
}
