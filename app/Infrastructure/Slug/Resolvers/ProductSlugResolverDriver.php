<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTtl;
use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\Cache;

class ProductSlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::PRODUCT;
    }

    /**
     * Resolve a product by its current or historical slug.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $value = trim($value);

        if ($value === '') {
            return ResolvedSlugDTO::notFound($value);
        }

        $cacheKey = CacheKey::productSlug($value);
        $cached = Cache::get($cacheKey);

        if ($cached === null) {
            $productSlug = ProductSlug::query()
                ->where('route_key', $value)
                ->first();

            $cached = [
                'product_id' => $productSlug?->product_id,
                'is_current' => $productSlug?->is_current ?? false,
            ];

            if ($productSlug === null) {
                Cache::put($cacheKey, $cached, CacheTtl::short());
            } else {
                Cache::forever($cacheKey, $cached);
            }
        }

        $productId = $cached['product_id'] ?? null;

        if (! is_numeric($productId)) {
            return ResolvedSlugDTO::notFound($value);
        }

        // Always recheck visibility, including scheduled publication and soft deletes.
        $model = Product::query()->find((int) $productId);

        if ($model === null || ! $model->isPubliclyVisible()) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentSlug = $model->getAttribute('slug');
        $currentSlugValue = is_string($currentSlug) ? $currentSlug : '';

        if ($cached['is_current'] && $value === $currentSlugValue) {
            return ResolvedSlugDTO::current(
                requestedValue: $value,
                currentValue: $currentSlugValue,
                model: $model,
            );
        }

        return ResolvedSlugDTO::redirect(
            requestedValue: $value,
            currentValue: $currentSlugValue,
            redirectTo: route('products.detail', $currentSlugValue),
            model: $model,
        );
    }
}
