<?php

namespace App\Application\Catalog\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetRelatedProductsAction
{
    public function execute(Product $product): Collection
    {
        return Cache::tags([
            CacheTag::PRODUCT_RELATED,
            CacheTag::PRODUCTS,
        ])->remember(
            CacheKey::productRelated($product->id, $product->updated_at->timestamp),
            CacheTtl::long(),
            function () use ($product) {

                $taxonomyIds = $product->tags
                    ->pluck('id')
                    ->merge($product->categories->pluck('id'))
                    ->filter()
                    ->values()
                    ->toArray();

                return Product::query()
                    ->whereHas('taxonomyTerms', function ($q) use ($taxonomyIds) {
                        $q->whereIn('taxonomy_terms.id', $taxonomyIds);
                    })
                    ->where('id', '!=', $product->id)
                    ->inRandomOrder()
                    ->limit(3)
                    ->get();
            }
        );
    }
}
