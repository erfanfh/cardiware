<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;

class TaxonomySlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return SlugScopeEnum::isTaxonomyScope($scope);
    }

    /**
     * Determine whether the given route key exists in the taxonomy scope.
     *
     * Note:
     * For hierarchical taxonomies, the provided slug value should be the final
     * route key that is stored in taxonomy_term_slugs.route_key.
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        $type = SlugScopeEnum::extractTaxonomyType($scope);

        return TaxonomyTermSlug::query()
            ->where('type', $type)
            ->where('route_key', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('taxonomy_term_id', '!=', $ignoreId)
            )
            ->exists();
    }
}
