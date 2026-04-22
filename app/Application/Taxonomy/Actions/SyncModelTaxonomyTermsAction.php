<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SyncModelTaxonomyTermsAction
{
    /**
     * @param Model $model
     * @param string $relation
     * @param TaxonomyKeyEnum $taxonomy
     * @param array<int, int|string|null> $ids
     * @return void
     */
    public function execute(Model $model, string $relation, TaxonomyKeyEnum $taxonomy, array $ids): void
    {
        if (!method_exists($model, $relation)) {
            return;
        }

        $normalizedIds = $this->normalizeIds($ids);

        $validIds = [];

        if (count($normalizedIds) !== 0) {
            $validIds = TaxonomyTerm::query()
                ->forTaxonomy($taxonomy)
                ->whereIn('id', $normalizedIds)
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->all();
        }

        $relationInstance = $model->{$relation}();

        if (!method_exists($relationInstance, 'sync')) {
            throw new InvalidArgumentException("Relation [{$relation}] is not syncable.");
        }

        $relationInstance->sync($validIds);
    }

    /**
     * @param array<int, int|string|null> $ids
     * @return array<int, int>
     */
    protected function normalizeIds(array $ids): array
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
