<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;

class SyncModelCategoriesAction
{
    /**
     * Sync categories for the given model.
     *
     * The target model must define a relation with the given name.
     * By default, this action expects a "categories" relation.
     *
     * @param Model $model
     * @param array<int, int|string|null> $payload
     * @return void
     */
    public function execute(Model $model, array $payload): void
    {
        if (!method_exists($model, 'categories')) {
            return;
        }

        $categoryIds = $this->normalizeIds($payload);
        $validCategoryIds = [];

        if (count($categoryIds) !== 0) {
            $validCategoryIds = TaxonomyTerm::query()
                ->where('type', TaxonomyTermTypeEnum::CATEGORY)
                ->whereIn('id', $categoryIds)
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->all();
        }

        $model->categories()->syncWithPivotValues(
            $validCategoryIds,
            [
                'type' => TaxonomyTermTypeEnum::CATEGORY,
            ]
        );
    }

    /**
     * Normalize incoming ids and remove invalid values.
     *
     * @param array<int, int|string|null> $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(function ($id): ?int {
                if ($id === null || $id === '') {
                    return null;
                }

                $normalizedId = (int)$id;

                return $normalizedId > 0 ? $normalizedId : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
