<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;

class RegeneratePathAndDescendantsAction
{
    protected GeneratePathAction $generatePathAction;

    public function __construct(GeneratePathAction $generatePathAction)
    {
        $this->generatePathAction = $generatePathAction;
    }

    /**
     * Regenerate path for term and all its descendants
     */
    public function execute(TaxonomyTerm $term): void
    {
        $term->path = $this->generatePathAction->execute($term);
        $term->saveQuietly();

        $term->children()->each(function (TaxonomyTerm $child) {
            $this->execute($child);
        });
    }
}
