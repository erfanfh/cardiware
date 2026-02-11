<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;

class RegeneratePathAndDescendantsAction
{
    protected GeneratePathAction $generatePathAction;

    /**
     * Constructor with GeneratePathAction dependency.
     *
     * @param GeneratePathAction $generatePathAction
     */

    public function __construct(GeneratePathAction $generatePathAction)
    {
        $this->generatePathAction = $generatePathAction;
    }

    /**
     * Regenerate path for term and all its descendants
     * @param TaxonomyTerm $term The taxonomy term
     * @return void
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
