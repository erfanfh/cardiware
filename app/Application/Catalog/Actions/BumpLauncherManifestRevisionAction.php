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

            // Integrity metadata is keyed by file ID and can otherwise remain valid for days.
            // Clear every file snapshot owned by this sale item so replacing a remote file at
            // the same URL cannot keep an old size or checksum alive.
            DB::table('launcher_download_file_integrities')
                ->where(function ($query) use ($saleItemId): void {
                    $query->where(function ($productFiles) use ($saleItemId): void {
                        $productFiles->where('source_type', 'product')
                            ->whereIn('source_file_id', function ($files) use ($saleItemId): void {
                                $files->select('id')
                                    ->from('product_sale_item_files')
                                    ->where('product_sale_item_id', $saleItemId);
                            });
                    })->orWhere(function ($updateFiles) use ($saleItemId): void {
                        $updateFiles->where('source_type', 'update')
                            ->whereIn('source_file_id', function ($files) use ($saleItemId): void {
                                $files->select('launcher_update_files.id')
                                    ->from('launcher_update_files')
                                    ->join(
                                        'launcher_updates',
                                        'launcher_updates.id',
                                        '=',
                                        'launcher_update_files.launcher_update_id'
                                    )
                                    ->where('launcher_updates.product_sale_item_id', $saleItemId);
                            });
                    })->orWhere(function ($modFiles) use ($saleItemId): void {
                        $modFiles->where('source_type', 'mod')
                            ->whereIn('source_file_id', function ($files) use ($saleItemId): void {
                                $files->select('launcher_mod_files.id')
                                    ->from('launcher_mod_files')
                                    ->join(
                                        'launcher_mods',
                                        'launcher_mods.id',
                                        '=',
                                        'launcher_mod_files.launcher_mod_id'
                                    )
                                    ->where('launcher_mods.product_sale_item_id', $saleItemId);
                            });
                    });
                })
                ->delete();

            // The launcher APIs do not cache manifests themselves, but catalog pages may.
            // Versioned keys prevent stale data even when the cache store has no tag support.
            Cache::forget("launcher:sale-item:{$saleItemId}:manifest");
            Cache::forget("launcher:sale-item:{$saleItemId}:catalog");

            return $revision;
        });
    }
}
