<?php

namespace App\Application\Vip\Actions;

use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Vip\Models\VipSaleItemClaim;
use Illuminate\Support\Facades\DB;

class TrackVipSaleItemFileDownloadAction
{
    public function remaining(VipSaleItemClaim $claim, ProductSaleItemFile $file): ?int
    {
        $limit = $claim->saleItem?->download_limit;
        if ($limit === null) {
            return null;
        }

        $count = DB::table('vip_sale_item_file_downloads')
            ->where('vip_sale_item_claim_id', $claim->getKey())
            ->where('product_sale_item_file_id', $file->getKey())
            ->value('downloads_count');

        return max(0, (int) $limit - (int) $count);
    }

    public function record(VipSaleItemClaim $claim, ProductSaleItemFile $file): void
    {
        DB::transaction(function () use ($claim, $file): void {
            $claim = VipSaleItemClaim::query()->whereKey($claim->getKey())
                ->lockForUpdate()->firstOrFail();
            $saleItem = $claim->saleItem;
            abort_unless($saleItem !== null
                && app(GetVipDownloadableSaleItemsAction::class)->query()
                    ->whereKey($saleItem->getKey())->exists()
                && $claim->user?->hasActiveVipSubscription(), 403);
            abort_unless(ProductSaleItemFile::query()
                ->whereKey($file->getKey())
                ->where('product_sale_item_id', $saleItem->getKey())
                ->where('is_active', true)->exists(), 403);

            $this->assertRemaining($claim, $file);
            DB::table('vip_sale_item_file_downloads')->insertOrIgnore([
                'vip_sale_item_claim_id' => $claim->getKey(),
                'product_sale_item_file_id' => $file->getKey(),
                'downloads_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('vip_sale_item_file_downloads')
                ->where('vip_sale_item_claim_id', $claim->getKey())
                ->where('product_sale_item_file_id', $file->getKey())
                ->increment('downloads_count', 1, [
                    'last_downloaded_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function assertRemaining(VipSaleItemClaim $claim, ProductSaleItemFile $file): void
    {
        $remaining = $this->remaining($claim, $file);
        abort_if($remaining !== null && $remaining <= 0, 403, 'سهمیه دانلود این فایل به پایان رسیده است.');
    }
}
