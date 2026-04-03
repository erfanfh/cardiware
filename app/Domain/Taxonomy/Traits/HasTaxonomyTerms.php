<?php

namespace App\Domain\Taxonomy\Traits;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomyTerms
{
    public function categories(): MorphToMany
    {
        return $this->taxonomyTerms()
            ->withPivot('type')
            ->wherePivot('type', TaxonomyTermTypeEnum::CATEGORY->value)
            ->withTimestamps();
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
        return $this->taxonomyTerms()
            ->withPivot('type')
            ->wherePivot('type', TaxonomyTermTypeEnum::TAG->value)
            ->withTimestamps();
    }
}
