<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Shared\Cache\Invalidators\TaxonomySlugCacheInvalidator;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class UpdateTaxonomyTermAction
{
    public function __construct(
        protected AssignSlugAction             $assignSlugAction,
        protected TaxonomySlugCacheInvalidator $taxonomySlugCacheInvalidator,
    )
    {
    }

    /**
     * @param TaxonomyTerm $term
     * @param TaxonomyKeyEnum $expectedTaxonomy
     * @param array<string, mixed> $data
     * @return TaxonomyTerm
     * @throws Throwable
     */
    public function execute(TaxonomyTerm $term, TaxonomyKeyEnum $expectedTaxonomy, array $data): TaxonomyTerm
    {
        return DB::transaction(function () use ($term, $expectedTaxonomy, $data) {
            if ($term->taxonomy !== $expectedTaxonomy) {
                throw new InvalidArgumentException(
                    'Taxonomy should be "' . $expectedTaxonomy->value . '" but "' . $term->taxonomy->value . '" given.'
                );
            }

            $termData = $data;

            unset(
                $termData['slug'],
                $termData['taxonomy'],
            );

            $term->update($termData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: $expectedTaxonomy->slugScope(),
                    model: $term,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->taxonomySlugCacheInvalidator->forgetForTermAndDescendants($term);

            return $term;
        });
    }
}
