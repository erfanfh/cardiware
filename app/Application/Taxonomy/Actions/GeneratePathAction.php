<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;

class GeneratePathAction
{
    /**
     * Generate path for a taxonomy term
     */
    public function execute(TaxonomyTerm $term): string
    {
        if (!$term->parent) {
            return $term->slug;
        }

        return $this->execute($term->parent) . '/' . $term->slug;
    }
}
