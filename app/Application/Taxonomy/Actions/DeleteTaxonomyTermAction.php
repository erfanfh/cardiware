<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Shared\Cache\Invalidators\TaxonomySlugCacheInvalidator;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

class DeleteTaxonomyTermAction
{
    public function __construct(
        protected TaxonomySlugCacheInvalidator $taxonomySlugCacheInvalidator,
    )
    {
    }

    /**
     * @param TaxonomyTerm $term
     * @return void
     */
    public function execute(TaxonomyTerm $term): void
    {
        $this->taxonomySlugCacheInvalidator->forgetForTerm($term);

        $term->delete();
    }
}
