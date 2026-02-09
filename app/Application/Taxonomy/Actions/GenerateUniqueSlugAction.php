<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;

class GenerateUniqueSlugAction
{
    /**
     * Generate a unique slug by checking both taxonomy_terms and taxonomy_term_slugs tables.
     *
     * @param string $baseSlug Base slug value
     * @param TaxonomyTermTypeEnum $type Taxonomy type (category/tag)
     * @param int|null $excludeId Term ID to exclude (for updates)
     * @return string Unique slug
     */
    public function execute(string $baseSlug, TaxonomyTermTypeEnum $type, ?int $excludeId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            // Check in main table
            $existsInTerms = TaxonomyTerm::where('slug', $slug)
                ->where('type', $type->value)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            // Check in slug history table
            $existsInSlugs = TaxonomyTermSlug::where('route_key', $slug)
                ->where('type', $type->value)
                ->when($excludeId, fn($q) => $q->where('taxonomy_term_id', '!=', $excludeId))
                ->exists();

            // Found unique slug
            if (!$existsInTerms && !$existsInSlugs) {
                return $slug;
            }

            // Append counter and retry
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }
}
