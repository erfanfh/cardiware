<?php

namespace App\Application\Vip\Actions;

use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Database\Eloquent\Builder;

class GetVipDownloadableSaleItemsAction
{
    public function query(): Builder
    {
        return ProductSaleItem::query()
            ->where('vip_access_enabled', true)
            ->where('launcher_enabled', false)
            ->where('status', ProductSaleItemStatusEnum::AVAILABLE)
            ->whereHas('product', fn (Builder $query) => $query
                ->where('status', ProductStatusEnum::ACTIVE)
                ->where('published_at', '<=', now()))
            ->whereHas('files', fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereNotNull('url')
                ->where('url', '<>', ''));
    }
}
