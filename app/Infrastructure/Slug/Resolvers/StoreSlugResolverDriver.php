<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTtl;
use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\Cache;

class StoreSlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::STORE;
    }

    /**
     * Resolve a store by its current or historical slug.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $value = trim($value);

        if ($value === '') {
            return ResolvedSlugDTO::notFound($value);
        }

        $cacheKey = CacheKey::storeSlug($value);
        $cached = Cache::get($cacheKey);

        if ($cached === null) {
            $storeSlug = StoreSlug::query()
                ->where('route_key', $value)
                ->first();

            $cached = [
                'store_id' => $storeSlug?->store_id,
                'is_current' => $storeSlug?->is_current ?? false,
            ];

            if ($storeSlug === null) {
                Cache::put($cacheKey, $cached, CacheTtl::short());
            } else {
                Cache::forever($cacheKey, $cached);
            }
        }

        $storeId = $cached['store_id'] ?? null;

        if (! is_numeric($storeId)) {
            return ResolvedSlugDTO::notFound($value);
        }

        // Load current store data and exclude soft-deleted stores on every request.
        $model = Store::query()->find((int) $storeId);

        if ($model === null) {
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
            redirectTo: route('stores.detail', $currentSlugValue),
            model: $model,
        );
    }
}
