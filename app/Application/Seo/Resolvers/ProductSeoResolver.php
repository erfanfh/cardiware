<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProductSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProductSeoDataAction $resolveProductSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        if (!$context->routeIs('products.detail')) {
            return false;
        }

        return true;
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $slug = $context->request->route('productSlug');
        $product = Product::where('slug', $slug)->first();

        return Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::COMMENT_AUTHORS,
            CacheTag::PRODUCTS,
            CacheTag::product($product->id),
            CacheTag::commentable($product->getMorphClass(), $product->getKey()),
        ])->remember(
            CacheKey::productSeoData(
                productId: $product->id,
                productUpdatedAt: $product->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $this->resolveProductSeoDataAction->execute($product)
        );
    }
}
