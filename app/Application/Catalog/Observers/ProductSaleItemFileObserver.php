<?php

namespace App\Application\Catalog\Observers;

use App\Application\Catalog\Actions\BumpLauncherManifestRevisionAction;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Launcher\Models\LauncherDownloadFileIntegrity;

class ProductSaleItemFileObserver
{
    public function __construct(
        private readonly BumpLauncherManifestRevisionAction $bumpRevisionAction,
    ) {
    }

    public function created(ProductSaleItemFile $file): void
    {
        $this->invalidateIntegrity($file);
        $this->bumpRevision($file->product_sale_item_id);
    }

    public function updated(ProductSaleItemFile $file): void
    {
        $this->invalidateIntegrity($file);
        $this->bumpRevisions([
            $file->product_sale_item_id,
            $file->getRawOriginal('product_sale_item_id'),
        ]);
    }

    public function deleted(ProductSaleItemFile $file): void
    {
        $this->invalidateIntegrity($file);
        $this->bumpRevision($file->product_sale_item_id);
    }

    private function invalidateIntegrity(ProductSaleItemFile $file): void
    {
        LauncherDownloadFileIntegrity::query()
            ->where('source_type', 'product')
            ->where('source_file_id', (int) $file->getKey())
            ->delete();
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

    private function bumpRevision(int $saleItemId): void
    {
        $this->bumpRevisions([$saleItemId]);
    }
}
