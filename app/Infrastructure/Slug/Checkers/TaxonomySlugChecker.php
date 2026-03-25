<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use InvalidArgumentException;

class TaxonomySlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(string $scope): bool
    {
        return str_starts_with($scope, 'taxonomy:');
    }

    /**
     * Determine whether the given route key exists in the taxonomy scope.
     *
     * Note:
     * For hierarchical taxonomies, the provided slug value should be the final
     * route key that is stored in taxonomy_term_slugs.route_key.
     */
    public function exists(
        string  $scope,
        string  $slug,
        ?int    $ignoreId = null,
        ?string $ignoreType = null,
    ): bool
    {
        $type = $this->extractTypeFromScope($scope);

        return TaxonomyTermSlug::query()
            ->where('type', $type)
            ->where('route_key', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('taxonomy_term_id', '!=', $ignoreId)
            )
            ->exists();
    }

    /**
     * Extract the taxonomy type from the given scope value.
     */
    private function extractTypeFromScope(string $scope): string
    {
        $parts = explode(':', $scope, 2);

        if (count($parts) !== 2 || trim($parts[1]) === '') {
            throw new InvalidArgumentException("Invalid taxonomy scope [{$scope}].");
        }

        return trim($parts[1]);
    }
}
