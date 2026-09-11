<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;

class GetPurchaseAccessFileDownloadsCountAction
{
    public function execute(PurchaseAccess $purchaseAccess, ProductSaleItemFile $file): int
    {
        // One row here is one download *session*, not one completed download. The
        // launcher opens a fresh session every time the server invalidates the previous
        // one (manifest-revision bump after an admin edit, lease expiry, file replaced),
        // and resumes the same partial file. Counting those exhausted a customer's whole
        // allowance without a single finished download ("403: download limit reached"
        // on release day). Sessions the server itself invalidated are therefore not the
        // customer's downloads and do not count.
        return PurchaseAccessDownload::query()
            ->where('purchase_access_id', $purchaseAccess->id)
            ->where('product_sale_item_file_id', $file->id)
            ->whereNull('invalidated_at')
            ->count();
    }
}
