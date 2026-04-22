<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Infrastructure\Slug\Services\TaxonomyRouteKeyBuilder;

class RefreshDescendantTaxonomyPathsAction
{
    public function __construct(
        protected TaxonomyRouteKeyBuilder $routeKeyBuilder,
    )
    {
    }

    /**
     * Rebuild paths and route-key history for all descendants of the given taxonomy term.
     *
     * @param TaxonomyTerm $term
     * @return void
     */
    public function execute(TaxonomyTerm $term): void
    {
        if (!$term->isHierarchical()) {
            return;
        }

        $children = TaxonomyTerm::query()
            ->forTaxonomy($term->taxonomy)
            ->where('parent_id', $term->id)
            ->get();

        foreach ($children as $child) {
            $this->refreshSingleTerm($child);
            $this->execute($child);
        }
    }

    /**
     * Refresh a single descendant path and its route-key history.
     *
     * @param TaxonomyTerm $term
     * @return void
     */
    protected function refreshSingleTerm(TaxonomyTerm $term): void
    {
        $currentPath = (string)($term->path ?? '');
        $newPath = $this->routeKeyBuilder->build($term, (string)$term->slug);

        if ($currentPath === $newPath) {
            return;
        }

        $term->forceFill([
            'path' => $newPath,
        ])->save();

        TaxonomyTermSlug::query()
            ->where('taxonomy_term_id', $term->getKey())
            ->where('is_current', true)
            ->update([
                'is_current' => false,
            ]);

        TaxonomyTermSlug::query()->updateOrCreate(
            [
                'taxonomy' => $term->taxonomy,
                'route_key' => $newPath,
            ],
            [
                'taxonomy_term_id' => $term->getKey(),
                'is_current' => true,
            ]
        );
    }
}
