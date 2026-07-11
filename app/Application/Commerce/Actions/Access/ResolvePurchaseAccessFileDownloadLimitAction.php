<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;

class ResolvePurchaseAccessFileDownloadLimitAction
{
    public function execute(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
    ): ?int
    {
        $fileLimit = data_get($file, 'download_limit');

        if ($fileLimit !== null) {
            return (int)$fileLimit;
        }

        return $purchaseAccess->download_limit !== null
            ? (int)$purchaseAccess->download_limit
            : null;
    }
}
