<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordPurchaseAccessDownloadAction
{
    public function __construct(
        private readonly HasPurchaseAccessFileReachedDownloadLimitAction $hasPurchaseAccessFileReachedDownloadLimitAction,
    )
    {
    }

    /**
     * Record a successful download attempt and increment total downloads count.
     *
     * @param array<string, mixed> $metadata
     */
    public function execute(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
        ?Request            $request = null,
        array               $metadata = [],
    ): PurchaseAccessDownload
    {
        return DB::transaction(function () use ($purchaseAccess, $file, $request, $metadata) {
            $purchaseAccess = PurchaseAccess::query()
                ->whereKey($purchaseAccess->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$purchaseAccess->isUsableForLauncher()) {
                throw new DomainException('Purchase access is not valid.');
            }

            if ((int)$file->product_sale_item_id !== (int)$purchaseAccess->product_sale_item_id) {
                throw new DomainException('File does not belong to this purchase access.');
            }

            if ($this->hasPurchaseAccessFileReachedDownloadLimitAction->execute($purchaseAccess, $file)) {
                throw new DomainException('File download limit has been reached.');
            }

            $download = PurchaseAccessDownload::query()->create([
                'purchase_access_id' => $purchaseAccess->id,
                'user_id' => $purchaseAccess->user_id,
                'product_id' => $purchaseAccess->product_id,
                'product_sale_item_id' => $purchaseAccess->product_sale_item_id,
                'product_sale_item_file_id' => $file->id,
                'file_name' => $this->resolveFileName($file),
                'file_disk' => $this->resolveFileDisk($file),
                'file_path' => $this->resolveFilePath($file),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'metadata' => $metadata,
            ]);

            $purchaseAccess->forceFill([
                'last_downloaded_at' => now(),
            ])->save();

            return $download;
        });
    }

    private function resolveFileName(ProductSaleItemFile $file): ?string
    {
        return $file->title
            ?? basename(parse_url((string)$file->url, PHP_URL_PATH) ?: '');
    }

    private function resolveFileDisk(ProductSaleItemFile $file): ?string
    {
        return 'external_url';
    }

    private function resolveFilePath(ProductSaleItemFile $file): ?string
    {
        return $file->url;
    }
}
