<?php

namespace App\Application\Catalog\Observers;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemManifest;

class ProductSaleItemManifestObserver
{
    public function created(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevision($manifest->product_sale_item_id);
    }

    public function updated(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevision($manifest->product_sale_item_id);
    }

    public function deleted(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevision($manifest->product_sale_item_id);
    }

    private function bumpRevision(int $saleItemId): void
    {
        ProductSaleItem::query()
            ->withTrashed()
            ->whereKey($saleItemId)
            ->increment('manifest_revision');
    }
}
