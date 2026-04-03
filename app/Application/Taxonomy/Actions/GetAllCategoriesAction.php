<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class GetAllCategoriesAction
{
    /**
     * Get all category options for form components.
     *
     * @return array<int, array{id:int,title:string,parent_id:int|null}>
     */
    public function execute(): array
    {
        return TaxonomyTerm::query()
            ->select(['id', 'title', 'parent_id'])
            ->where('type', TaxonomyTermTypeEnum::CATEGORY)
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
