<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Support\Facades\Cache;

class TaxonomySlugCacheInvalidator
{
    public function forgetBySlug(string $taxonomy, string $routeKey): void
    {
        Cache::forget(CacheKey::taxonomySlug($taxonomy, $routeKey));
    }

    public function forgetForTerm(TaxonomyTerm $term): void
    {
        $this->forgetForTermIds(
            taxonomy: $term->taxonomy->value,
            termIds: [$term->getKey()],
        );
    }

    /**
     * Forget all cached route keys for the given term and all of its descendants.
     *
     * This is required for hierarchical taxonomies because changing a parent's
     * slug rebuilds the path of every descendant. Both previous and current
     * route keys are stored in the slug history table, so invalidating all of
     * them also clears stale redirects and previously cached not-found results.
     */
    public function forgetForTermAndDescendants(TaxonomyTerm $term): void
    {
        if (!$term->isHierarchical()) {
            $this->forgetForTerm($term);

            return;
        }

        $this->forgetForTermIds(
            taxonomy: $term->taxonomy->value,
            termIds: $term->selfAndDescendantIds(),
        );
    }

    /**
     * @param array<int, int|string> $termIds
     */
    protected function forgetForTermIds(string $taxonomy, array $termIds): void
    {
        if ($termIds === []) {
            return;
        }

        TaxonomyTermSlug::query()
            ->where('taxonomy', $taxonomy)
            ->whereIn('taxonomy_term_id', $termIds)
            ->pluck('route_key')
            ->filter(fn($routeKey) => is_string($routeKey) && $routeKey !== '')
            ->unique()
            ->each(fn(string $routeKey) => $this->forgetBySlug($taxonomy, $routeKey));
    }
}
