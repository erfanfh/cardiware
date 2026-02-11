<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;

class BulkDeleteAction
{
    /**
     * Handle bulk delete operation on selected taxonomy terms.
     *
     * @param array $ids
     * @return int Number of deleted terms
     */
    public function execute(array $ids): int
    {
        // Preserve children by setting their parent_id to null
        foreach ($ids as $id) {
            $term = TaxonomyTerm::find($id);

            if ($term && $term->children()->exists()) {
                $term->children()->update(['parent_id' => null]);
            }
        }

        // Delete the selected terms
        return TaxonomyTerm::whereIn('id', $ids)->delete();
    }
}
