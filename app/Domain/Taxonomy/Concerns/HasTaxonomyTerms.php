<?php

namespace App\Domain\Taxonomy\Concerns;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomyTerms
{
    /**
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->taxonomyTerms()->where('type', 'category');
    }

    /**
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
        );
    }

    /**
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->taxonomyTerms()->where('type', 'tag');
    }
}
