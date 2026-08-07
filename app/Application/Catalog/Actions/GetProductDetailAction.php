<?php

namespace App\Application\Catalog\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Cache;

final class GetProductDetailAction
{
    public function execute(Product $product): Product
    {
        return Cache::tags([
            CacheTag::PRODUCT_DETAIL,
            CacheTag::PRODUCTS,
        ])->remember(
            CacheKey::productDetail($product->id, $product->updated_at->timestamp),
            CacheTtl::long(),
            fn() => $product->load([
                'displaySetting',
                'categories',
                'tags',
                'picture.primaryVariant',
                'picture.variants',
                'imageGallery.mediaFile.primaryVariant',
                'imageGallery.mediaFile.variants',
                'teamMembers.teamMember.picture.primaryVariant',
                'teamMembers.teamMember.picture.variants',
                'requirements',
                'installationSteps',
                'faqs',
                'releases.releaseChanges',
                'informationEntries.field.icon',
                'store.picture.primaryVariant',
                'store.picture.variants',
                'updateProgress',
                'activeMedals.picture.primaryVariant',
            ])
        );
    }
}
