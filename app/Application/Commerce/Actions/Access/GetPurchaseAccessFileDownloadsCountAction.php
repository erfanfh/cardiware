<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;

class GetPurchaseAccessFileDownloadsCountAction
{
    public function execute(PurchaseAccess $purchaseAccess, ProductSaleItemFile $file): int
    {
        return PurchaseAccessDownload::query()
            ->where('purchase_access_id', $purchaseAccess->id)
            ->where('product_sale_item_file_id', $file->id)
            ->count();
    }
}
