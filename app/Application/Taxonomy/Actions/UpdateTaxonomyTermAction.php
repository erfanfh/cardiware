<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;

final class UpdateTaxonomyTermAction
{
    public function execute(TaxonomyTerm $term, array $data): TaxonomyTerm
    {
        return DB::transaction(function () use ($term, $data) {

            $oldSlug     = $term->slug;
            $oldParentId = $term->parent_id;

            $term->fill([
                'title'       => $data['title'],
                'slug'        => $data['slug'] ?? $term->generateSlugFromTitle(),
                'parent_id'   => $data['parent_id'] ?? $term->parent_id,
                'description' => $data['description'] ?? $term->description,
            ]);

            $slugChanged   = $oldSlug !== $term->slug;
            $parentChanged = $oldParentId != $term->parent_id;

            $term->save();

            if ($slugChanged) {
                $term->recordSlugHistory($oldSlug);
            }

            if ($slugChanged || $parentChanged) {
                $term->regeneratePathAndDescendants();
            }

            return $term;
        });
    }
}
