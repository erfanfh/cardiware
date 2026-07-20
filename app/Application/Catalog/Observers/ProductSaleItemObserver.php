<?php

namespace App\Application\Catalog\Observers;

use App\Domain\Catalog\Models\ProductSaleItem;

class ProductSaleItemObserver
{
    public function updated(ProductSaleItem $saleItem): void
    {
        if (!$saleItem->wasChanged([
            'sku',
            'title',
            'edition_key',
            'version',
        ])) {
            return;
        }

        ProductSaleItem::query()
            ->withTrashed()
            ->whereKey($saleItem->getKey())
            ->increment('manifest_revision');
    }
}
