<?php

namespace App\Domain\Taxonomy\Traits;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomyTerms
{
    /**
     * Base taxonomy terms relation.
     *
     * @return MorphToMany
     */
    public function taxonomyTerms(): MorphToMany
    {
        return $this->morphToMany(
            TaxonomyTerm::class,
            'taxonomable',
            'taxonomables',
            'taxonomable_id',
            'taxonomy_term_id'
        )->withTimestamps();
    }

    /**
     * Taxonomy terms relation filtered by taxonomy key.
     *
     * @param TaxonomyKeyEnum $taxonomy
     * @return MorphToMany
     */
    public function taxonomyTermsFor(TaxonomyKeyEnum $taxonomy): MorphToMany
    {
        return $this->taxonomyTerms()
            ->where('taxonomy', $taxonomy->value);
    }
}
