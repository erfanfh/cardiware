<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use Illuminate\Support\Collection;

class HydratePurchaseAccessFileDownloadStatsAction
{
    public function __construct(
        private readonly ResolvePurchaseAccessFileDownloadLimitAction $resolveDownloadLimitAction,
    )
    {
    }

    /**
     * @param Collection<int, PurchaseAccess> $purchaseAccesses
     */
    public function execute(Collection $purchaseAccesses): void
    {
        if ($purchaseAccesses->isEmpty()) {
            return;
        }

        $downloadCounts = PurchaseAccessDownload::query()
            ->whereIn(
                'purchase_access_id',
                $purchaseAccesses->pluck('id')
            )
            ->selectRaw(
                'purchase_access_id, product_sale_item_file_id, COUNT(*) as downloads_count'
            )
            ->groupBy([
                'purchase_access_id',
                'product_sale_item_file_id',
            ])
            ->get()
            ->mapWithKeys(function (PurchaseAccessDownload $row): array {
                $key = $this->makeKey(
                    purchaseAccessId: (int)$row->purchase_access_id,
                    fileId: (int)$row->product_sale_item_file_id,
                );

                return [
                    $key => (int)$row->getAttribute('downloads_count'),
                ];
            });

        $purchaseAccesses->each(
            function (PurchaseAccess $purchaseAccess) use ($downloadCounts): void {
                if (
                    !$purchaseAccess->relationLoaded('productSaleItem')
                    || $purchaseAccess->productSaleItem === null
                    || !$purchaseAccess->productSaleItem->relationLoaded('files')
                ) {
                    return;
                }

                $accessIsUsable = $purchaseAccess->isUsableForLauncher();

                $purchaseAccess->productSaleItem->files->each(
                    function (ProductSaleItemFile $file) use (
                        $purchaseAccess,
                        $downloadCounts,
                        $accessIsUsable,
                    ): void {
                        $key = $this->makeKey(
                            purchaseAccessId: (int)$purchaseAccess->getKey(),
                            fileId: (int)$file->getKey(),
                        );

                        $downloadsCount = (int)$downloadCounts->get($key, 0);

                        $downloadLimit = $this->resolveDownloadLimitAction->execute(
                            purchaseAccess: $purchaseAccess,
                            file: $file,
                        );

                        $remainingDownloads = $downloadLimit === null
                            ? null
                            : max(0, $downloadLimit - $downloadsCount);

                        $file->setAttribute(
                            'downloads_count',
                            $downloadsCount
                        );

                        $file->setAttribute(
                            'resolved_download_limit',
                            $downloadLimit
                        );

                        $file->setAttribute(
                            'remaining_downloads',
                            $remainingDownloads
                        );

                        $file->setAttribute(
                            'can_download',
                            $accessIsUsable
                            && (
                                $remainingDownloads === null
                                || $remainingDownloads > 0
                            )
                        );
                    }
                );
            }
        );
    }

    private function makeKey(
        int $purchaseAccessId,
        int $fileId,
    ): string
    {
        return "{$purchaseAccessId}:{$fileId}";
    }
}
