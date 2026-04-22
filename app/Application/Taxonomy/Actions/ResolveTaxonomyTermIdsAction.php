<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Throwable;

class ResolveTaxonomyTermIdsAction
{
    public function __construct(
        protected CreateTaxonomyTermAction $createTaxonomyTermAction,
    )
    {
    }

    /**
     * @param TaxonomyKeyEnum $taxonomy
     * @param array<string, mixed> $payload
     * @return array<int>
     * @throws Throwable
     */
    public function execute(TaxonomyKeyEnum $taxonomy, array $payload): array
    {
        $existingIds = collect(data_get($payload, 'existing', []))
            ->map(fn($id) => (int)$id)
            ->filter()
            ->values();

        $validatedExistingIds = TaxonomyTerm::query()
            ->forTaxonomy($taxonomy)
            ->whereIn('id', $existingIds)
            ->pluck('id');

        $newTitles = collect(data_get($payload, 'new', []))
            ->map(fn($title) => preg_replace('/\s+/u', ' ', trim((string)$title)))
            ->filter()
            ->unique(fn($title) => mb_strtolower($title))
            ->values();

        $newIds = $newTitles->map(function (string $title) use ($taxonomy) {
            $existing = TaxonomyTerm::query()
                ->forTaxonomy($taxonomy)
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
                ->first();

            if ($existing) {
                return $existing->id;
            }

            $term = $this->createTaxonomyTermAction->execute($taxonomy, [
                'title' => $title,
                'slug' => $title,
            ]);

            return $term->id;
        });

        return $validatedExistingIds
            ->merge($newIds)
            ->unique()
            ->values()
            ->all();
    }
}
