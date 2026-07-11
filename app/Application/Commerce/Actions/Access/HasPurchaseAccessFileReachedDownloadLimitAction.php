<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;

class HasPurchaseAccessFileReachedDownloadLimitAction
{
    public function __construct(
        private readonly ResolvePurchaseAccessFileDownloadLimitAction $resolveDownloadLimitAction,
        private readonly GetPurchaseAccessFileDownloadsCountAction    $getDownloadsCountAction,
    )
    {
    }

    public function execute(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
    ): bool
    {
        $limit = $this->resolveDownloadLimitAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        if ($limit === null) {
            return false;
        }

        $downloadsCount = $this->getDownloadsCountAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        return $downloadsCount >= $limit;
    }

    public function remaining(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
    ): ?int
    {
        $limit = $this->resolveDownloadLimitAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        if ($limit === null) {
            return null;
        }

        $downloadsCount = $this->getDownloadsCountAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        return max(0, $limit - $downloadsCount);
    }
}
