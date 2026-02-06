<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;

class GeneratePathAction
{
    /**
     * Generate path for a taxonomy term
     *
     * @param TaxonomyTerm $term The taxonomy term
     * @return string Generated path (e.g., "parent/child/grandchild")
     */
    public function execute(TaxonomyTerm $term): string
    {
        if (!$term->parent) {
            return $term->slug;
        }

        return $this->execute($term->parent) . '/' . $term->slug;
    }
}
