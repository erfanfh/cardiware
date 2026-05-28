<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class GetAllTaxonomiesAction
{
    /**
     * @return array<int, array{id:int,title:string,parent_id:int|null}>
     */
    public function execute(TaxonomyKeyEnum $taxonomyKey): array
    {
        return TaxonomyTerm::query()
            ->select(['id', 'title', 'parent_id'])
            ->forTaxonomy($taxonomyKey)
            ->orderBy('title')
            ->get()
            ->map(fn(TaxonomyTerm $term) => [
                'id' => (int)$term->id,
                'title' => (string)$term->title,
                'parent_id' => $term->parent_id ? (int)$term->parent_id : null,
            ])
            ->all();
    }
}
