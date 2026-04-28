<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Shared\Cache\Invalidators\TaxonomySlugCacheInvalidator;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTaxonomyTermAction
{
    public function __construct(
        protected AssignSlugAction             $assignSlugAction,
        protected TaxonomySlugCacheInvalidator $taxonomySlugCacheInvalidator,
    )
    {
    }

    /**
     * @param TaxonomyKeyEnum $taxonomy
     * @param array<string, mixed> $data
     * @return TaxonomyTerm
     * @throws Throwable
     */
    public function execute(TaxonomyKeyEnum $taxonomy, array $data): TaxonomyTerm
    {
        return DB::transaction(function () use ($taxonomy, $data) {
            $termData = Arr::except($data, ['slug']);
            $termData['taxonomy'] = $taxonomy;

            $term = TaxonomyTerm::create($termData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: $taxonomy->slugScope(),
                    model: $term,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->taxonomySlugCacheInvalidator->forgetForTerm($term);

            return $term;
        });
    }
}
