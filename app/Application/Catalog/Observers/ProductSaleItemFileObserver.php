<?php

namespace App\Application\Catalog\Observers;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;

class ProductSaleItemFileObserver
{
    public function created(ProductSaleItemFile $file): void
    {
        $this->bumpRevision($file->product_sale_item_id);
    }

    public function updated(ProductSaleItemFile $file): void
    {
        $this->bumpRevision($file->product_sale_item_id);
    }

    public function deleted(ProductSaleItemFile $file): void
    {
        $this->bumpRevision($file->product_sale_item_id);
    }

    private function bumpRevision(int $saleItemId): void
    {
        ProductSaleItem::query()
            ->withTrashed()
            ->whereKey($saleItemId)
            ->increment('manifest_revision');
    }
}
