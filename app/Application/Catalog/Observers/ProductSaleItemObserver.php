<?php

namespace App\Application\Catalog\Observers;

use App\Application\Catalog\Actions\BumpLauncherManifestRevisionAction;
use App\Application\Shared\Cache\Invalidators\LauncherCacheInvalidator;
use App\Domain\Catalog\Models\ProductSaleItem;

class ProductSaleItemObserver
{
    public function __construct(
        private readonly BumpLauncherManifestRevisionAction $bumpRevisionAction,
        private readonly LauncherCacheInvalidator $cacheInvalidator,
    ) {
    }

    public function updated(ProductSaleItem $saleItem): void
    {
        // Availability affects catalog visibility/actions, not the installed content
        // identity. Refresh caches without invalidating customers' download sessions.
        if ($saleItem->wasChanged('status')) {
            $this->cacheInvalidator->flushSaleItem((int) $saleItem->getKey());
        }

        if (!$saleItem->wasChanged([
            'sku',
            'title',
            'edition_key',
            'version',
            'launcher_enabled',
            'staging_user_ids',
            'launcher_free_claim_enabled',
            'launcher_title',
            'launcher_excerpt',
            'launcher_cover_media_id',
            'launcher_sort_order',
            'launcher_support_enabled',
            'launcher_support_url',
            'launcher_usage_guide_enabled',
            'launcher_usage_guide_content',
        ])) {
            return;
        }

        $this->bumpRevisionAction->execute((int) $saleItem->getKey());
    }
}
