<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;

class TaxonomySlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     *
     * @param SlugScopeEnum $scope
     * @return bool
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return SlugScopeEnum::isTaxonomyScope($scope);
    }

    /**
     * Determine whether the given route key exists in the taxonomy scope.
     *
     * @param SlugScopeEnum $scope
     * @param string $slug
     * @param int|null $ignoreId
     * @param string|null $ignoreType
     * @return bool
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        $taxonomy = SlugScopeEnum::extractTaxonomyEnum($scope);

        return TaxonomyTermSlug::query()
            ->where('taxonomy', $taxonomy)
            ->where('route_key', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('taxonomy_term_id', '!=', $ignoreId)
            )
            ->exists();
    }
}
