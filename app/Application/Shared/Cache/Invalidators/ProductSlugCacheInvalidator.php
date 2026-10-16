<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSlug;
use Illuminate\Support\Facades\Cache;

final class ProductSlugCacheInvalidator
{
    public function forgetBySlug(string $slug): void
    {
        if (trim($slug) !== '') {
            Cache::forget(CacheKey::productSlug($slug));
        }
    }

    public function forgetForModel(Product $product): void
    {
        $slugs = ProductSlug::query()
            ->where('product_id', $product->getKey())
            ->pluck('route_key')
            ->push($product->slug)
            ->filter()
            ->unique();

        foreach ($slugs as $slug) {
            $this->forgetBySlug($slug);
        }
    }
}
