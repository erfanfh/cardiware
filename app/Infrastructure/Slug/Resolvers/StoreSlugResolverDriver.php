<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Marketplace\Models\StoreSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Model;

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
     * Resolve a store by its current slug.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $storeSlug = StoreSlug::query()
            ->where('route_key', $value)
            ->first();

        if ($storeSlug === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        /** @var Model|null $model */
        $model = $storeSlug->store;

        if ($model === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentSlug = $model->getAttribute('slug');
        $currentSlugValue = is_string($currentSlug) ? $currentSlug : '';

        if ($storeSlug->is_current) {
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
