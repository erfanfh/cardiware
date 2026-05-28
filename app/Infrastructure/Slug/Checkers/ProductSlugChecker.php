<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Catalog\Models\ProductSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;

class ProductSlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::PRODUCT;
    }

    /**
     * Determine whether the given slug exists in the product scope.
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        return ProductSlug::query()
            ->where('route_key', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('product_id', '!=', $ignoreId)
            )
            ->exists();
    }
}
