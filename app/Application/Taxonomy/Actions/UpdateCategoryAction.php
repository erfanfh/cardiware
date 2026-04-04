<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Throwable;

class UpdateCategoryAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {
    }

    /**
     * @param TaxonomyTerm $category
     * @param array $data
     * @return TaxonomyTerm
     * @throws Throwable
     */
    public function execute(TaxonomyTerm $category, array $data): TaxonomyTerm
    {
        Gate::authorize('category.edit');

        return DB::transaction(function () use ($category, $data) {
            if ($category->type !== TaxonomyTermTypeEnum::CATEGORY)
                throw new InvalidArgumentException('Taxonomy type should be "' . TaxonomyTermTypeEnum::CATEGORY->value . '" but "' . $category->type->value . '" given.');

            $categoryData = $data;

            unset(
                $categoryData['slug'],
                $categoryData['type'],
            );

            $category->update($categoryData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::TAXONOMY__CATEGORY,
                    model: $category,
                    desiredSlug: $desiredSlug,
                )
            );

            return $category;
        });
    }
}
