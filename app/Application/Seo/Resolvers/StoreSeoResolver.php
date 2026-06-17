<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveStoreSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Support\Facades\Cache;

class StoreSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveStoreSeoDataAction $resolveStoreSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('stores.detail');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $slug = $context->request->route('storeSlug');
        $store = Store::where('slug', $slug)->first();

        return Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::STORES,
            CacheTag::store($store->id),
        ])->remember(
            CacheKey::storeSeoData(
                storeId: $store->id,
                storeUpdatedAt: $store->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $this->resolveStoreSeoDataAction->execute($store)
        );
    }
}
