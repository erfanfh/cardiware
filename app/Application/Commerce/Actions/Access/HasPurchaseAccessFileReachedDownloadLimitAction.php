<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;

class HasPurchaseAccessFileReachedDownloadLimitAction
{
    public function __construct(
        private readonly GetPurchaseAccessFileDownloadsCountAction $getPurchaseAccessFileDownloadsCountAction,
    )
    {
    }

    public function execute(PurchaseAccess $purchaseAccess, ProductSaleItemFile $file): bool
    {
        $limit = $this->resolveDownloadLimit($purchaseAccess, $file);

        if ($limit === null) {
            return false;
        }

        $downloadsCount = $this->getPurchaseAccessFileDownloadsCountAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        return $downloadsCount >= $limit;
    }

    public function remaining(PurchaseAccess $purchaseAccess, ProductSaleItemFile $file): ?int
    {
        $limit = $this->resolveDownloadLimit($purchaseAccess, $file);

        if ($limit === null) {
            return null;
        }

        $downloadsCount = $this->getPurchaseAccessFileDownloadsCountAction->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        return max(0, $limit - $downloadsCount);
    }

    private function resolveDownloadLimit(PurchaseAccess $purchaseAccess, ProductSaleItemFile $file): ?int
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
