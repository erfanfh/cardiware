<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Shared\Cache\Invalidators\PublicReadCacheInvalidator;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

class SyncModelTaxonomyTermsAction
{
    /**
     * @param  array<int, int|string|null>  $ids
     */
    public function execute(Model $model, string $relation, TaxonomyKeyEnum $taxonomy, array $ids): void
    {
        if (! method_exists($model, $relation)) {
            return;
        }

        $normalizedIds = $this->normalizeIds($ids);

        $validIds = [];

        if (count($normalizedIds) !== 0) {
            $validIds = TaxonomyTerm::query()
                ->forTaxonomy($taxonomy)
                ->whereIn('id', $normalizedIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $relationInstance = $model->{$relation}();

        if (! $relationInstance instanceof BelongsToMany) {
            throw new InvalidArgumentException("Relation [{$relation}] is not syncable.");
        }

        /*
         * Important:
         * Do not use sync() here.
         * sync() detaches all related taxonomy terms that are not in $validIds,
         * including terms from other taxonomies.
         */

        $currentTaxonomyIds = $model->{$relation}()
            ->forTaxonomy($taxonomy)
            ->pluck($relationInstance->getRelated()->getQualifiedKeyName())
            ->map(fn ($id) => (int) $id)
            ->all();

        $idsToDetach = array_values(array_diff($currentTaxonomyIds, $validIds));

        if (count($idsToDetach) !== 0) {
            app(AuditService::class)->detach($model, $relation, $idsToDetach);
        }

        if (count($validIds) !== 0) {
            app(AuditService::class)->sync($model, $relation, $validIds, false);
        }

        if ($idsToDetach !== [] || array_diff($validIds, $currentTaxonomyIds) !== []) {
            app(PublicReadCacheInvalidator::class)->forModel($model);
        }
    }

    /**
     * @param  array<int, int|string|null>  $ids
     * @return array<int, int>
     */
    protected function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(function ($id): ?int {
                if ($id === null || $id === '') {
                    return null;
                }

                $normalizedId = (int) $id;

                return $normalizedId > 0 ? $normalizedId : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
