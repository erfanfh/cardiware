<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Validation\ValidationException;

class RecordSlugHistoryAction
{
    /**
     * Save history of slug when it changes
     *
     * @param TaxonomyTerm $term The taxonomy term
     * @param string $oldSlug The previous slug value
     * @return void
     * @throws ValidationException If new slug already exists
     */
    public function execute(TaxonomyTerm $term, string $oldSlug): void
    {
        if (empty($oldSlug) || $oldSlug === $term->slug) {
            return;
        }

        // Check if slug exists for other terms
        $existsInTerms = TaxonomyTerm::where('slug', $term->slug)
            ->where('id', '!=', $term->id)
            ->exists();

        $existsInSlugs = TaxonomyTermSlug::where('route_key', $term->slug)
            ->where('type', $term->type->value)
            ->where('taxonomy_term_id', '!=', $term->id)
            ->exists();

        if ($existsInTerms || $existsInSlugs) {
            throw new ValidationException(
                validator(['slug' => $term->slug], [
                    'slug' => 'unique:taxonomy_terms,slug'
                ])
            );
        }

        // Deactivate current slugs for this term
        $term->slugs()->where('is_current', true)->update(['is_current' => false]);

        // Create or update old slug record
        $term->slugs()->updateOrCreate(
            [
                'route_key' => $oldSlug,
                'type' => $term->type->value,
            ],
            [
                'is_current' => false,
                'taxonomy_term_id' => $term->id,
            ]
        );

        // Create or update new slug record
        $term->slugs()->updateOrCreate(
            [
                'route_key' => $term->slug,
                'type' => $term->type->value,
            ],
            [
                'is_current' => true,
                'taxonomy_term_id' => $term->id,
            ]
        );
    }
}
