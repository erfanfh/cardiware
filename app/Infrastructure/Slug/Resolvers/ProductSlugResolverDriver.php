<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Catalog\Models\ProductSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;

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
     * Resolve a product by its current slug.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $productSlug = ProductSlug::query()
            ->where('route_key', $value)
            ->first();

        if ($productSlug === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        return ResolvedSlugDTO::current(
            requestedValue: $value,
            currentValue: $value,
            model: $productSlug->product,
        );
    }
}
