<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class BumpLauncherManifestRevisionAction
{
    public function execute(int $saleItemId): int
    {
        return DB::transaction(function () use ($saleItemId): int {
            $saleItem = ProductSaleItem::query()
                ->withTrashed()
                ->whereKey($saleItemId)
                ->lockForUpdate()
                ->first();

            if (!$saleItem instanceof ProductSaleItem) {
                return 0;
            }

            $revision = max(0, (int) $saleItem->manifest_revision) + 1;

            DB::table('product_sale_items')
                ->where('id', $saleItemId)
                ->update([
                    'manifest_revision' => $revision,
                    'updated_at' => now(),
                ]);

            PurchaseAccessDownload::query()
                ->where('product_sale_item_id', $saleItemId)
                ->whereNull('invalidated_at')
                ->update([
                    'invalidated_at' => now(),
                    'session_expires_at' => now(),
                    'updated_at' => now(),
                ]);

            LauncherDownloadSession::query()
                ->whereIn('purchase_access_id', function ($query) use ($saleItemId): void {
                    $query->select('id')
                        ->from('purchase_accesses')
                        ->where('product_sale_item_id', $saleItemId);
                })
                ->whereNull('invalidated_at')
                ->update([
                    'invalidated_at' => now(),
                    'session_expires_at' => now(),
                    'updated_at' => now(),
                ]);

            // Integrity snapshots are deliberately NOT deleted here any more. The resolver
            // now validates a snapshot against the file record's registered size/sha256
            // (RemoteDownloadFileMetadataResolver::canReuseVerifiedMetadata), so a replaced
            // file is detected without re-verifying every untouched part after each save.

            // The launcher APIs do not cache manifests themselves, but catalog pages may.
            // Versioned keys prevent stale data even when the cache store has no tag support.
            Cache::forget("launcher:sale-item:{$saleItemId}:manifest");
            Cache::forget("launcher:sale-item:{$saleItemId}:catalog");

            return $revision;
        });
    }
}
