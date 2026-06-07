<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use DomainException;

class EnsurePurchaseAccessCanDownloadFileAction
{
    public function __construct(
        private readonly CanUsePurchaseAccessAction $canUsePurchaseAccessAction,
    )
    {
    }

    /**
     * Ensure purchase access can download the given file.
     */
    public function execute(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
        int                 $userId,
    ): void
    {
        if ((int)$purchaseAccess->user_id !== $userId) {
            throw new DomainException('Purchase access does not belong to the current user.');
        }

        if ((int)$file->product_sale_item_id !== (int)$purchaseAccess->product_sale_item_id) {
            throw new DomainException('File does not belong to this purchase access.');
        }

        if (!$this->canUsePurchaseAccessAction->execute($purchaseAccess)) {
            throw new DomainException('Purchase access is not usable.');
        }
    }
}
