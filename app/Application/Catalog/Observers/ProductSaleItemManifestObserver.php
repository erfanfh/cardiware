<?php

namespace App\Application\Catalog\Observers;

use App\Application\Catalog\Actions\BumpLauncherManifestRevisionAction;
use App\Domain\Catalog\Models\ProductSaleItemManifest;

class ProductSaleItemManifestObserver
{
    public function __construct(
        private readonly BumpLauncherManifestRevisionAction $bumpRevisionAction,
    ) {
    }

    public function created(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevision($manifest->product_sale_item_id);
    }

    public function updated(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevisions([
            $manifest->product_sale_item_id,
            $manifest->getRawOriginal('product_sale_item_id'),
        ]);
    }

    public function deleted(ProductSaleItemManifest $manifest): void
    {
        $this->bumpRevision($manifest->product_sale_item_id);
    }

    private function bumpRevision(int $saleItemId): void
    {
        $this->bumpRevisions([$saleItemId]);
    }

    /**
     * @param array<int, mixed> $saleItemIds
     */
    private function bumpRevisions(array $saleItemIds): void
    {
        foreach (array_unique(array_filter(array_map(
            static fn (mixed $id): ?int => is_numeric($id) && (int) $id > 0 ? (int) $id : null,
            $saleItemIds,
        ))) as $saleItemId) {
            $this->bumpRevisionAction->execute($saleItemId);
        }
    }
}
