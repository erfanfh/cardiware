<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use Illuminate\Support\Facades\DB;

class ResetPurchaseAccessDownloadsAction
{
    public function execute(PurchaseAccess $purchaseAccess): int
    {
        return DB::transaction(function () use ($purchaseAccess): int {
            $lockedPurchaseAccess = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->whereKey($purchaseAccess->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $deletedDownloadsCount = PurchaseAccessDownload::query()
                ->where('purchase_access_id', $lockedPurchaseAccess->getKey())
                ->delete();

            if ($lockedPurchaseAccess->last_downloaded_at !== null) {
                $lockedPurchaseAccess->forceFill([
                    'last_downloaded_at' => null,
                ])->save();
            }

            return $deletedDownloadsCount;
        });
    }
}
