<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomiesImporter
{
    public function count(): int
    {
        return $this->countTaxonomy('category')
            + $this->countTaxonomy('category')
            + $this->countTaxonomy('post_tag');
    }

    public function import(?Closure $progressCallback = null): void
    {
        $this->importCategories($progressCallback);
        $this->syncCategoryParentsAndPaths($progressCallback);
        $this->importTags($progressCallback);
    }

    private function countTaxonomy(string $wpTaxonomy): int
    {
        return DB::connection('wordpress')
            ->table('terms')
            ->join('term_taxonomy', 'terms.term_id', '=', 'term_taxonomy.term_id')
            ->where('term_taxonomy.taxonomy', $wpTaxonomy)
            ->count();
    }

    private function importCategories(?Closure $progressCallback = null): void
    {
        $this->importTaxonomy(
            wpTaxonomy: 'category',
            sourceType: 'wordpress_category',
            taxonomy: TaxonomyKeyEnum::POST_CATEGORY,
            progressCallback: $progressCallback
        );
    }

    private function importTags(?Closure $progressCallback = null): void
    {
        $this->importTaxonomy(
            wpTaxonomy: 'post_tag',
            sourceType: 'wordpress_tag',
            taxonomy: TaxonomyKeyEnum::POST_TAG,
            progressCallback: $progressCallback
        );
    }

    private function importTaxonomy(
        string          $wpTaxonomy,
        string          $sourceType,
        TaxonomyKeyEnum $taxonomy,
        ?Closure        $progressCallback = null
    ): void
    {
        DB::connection('wordpress')
            ->table('terms')
            ->join('term_taxonomy', 'terms.term_id', '=', 'term_taxonomy.term_id')
            ->where('term_taxonomy.taxonomy', $wpTaxonomy)
            ->select([
                'terms.term_id',
                'terms.name',
                'terms.slug',
                'term_taxonomy.term_taxonomy_id',
                'term_taxonomy.description',
                'term_taxonomy.parent',
                'term_taxonomy.count',
            ])
            ->orderBy('terms.term_id')
            ->chunk(500, function ($terms) use ($sourceType, $taxonomy, $progressCallback) {
                foreach ($terms as $wpTerm) {
                    $this->importTerm($wpTerm, $sourceType, $taxonomy);

                    if ($progressCallback) {
                        $progressCallback();
                    }
                }
            });
    }

    private function importTerm(object $wpTerm, string $sourceType, TaxonomyKeyEnum $taxonomy): void
    {
        $exists = WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $wpTerm->term_taxonomy_id)
            ->where('target_type', TaxonomyTerm::class)
            ->exists();

        if ($exists) {
            return;
        }

        $slug = $this->makeUniqueSlug(
            taxonomy: $taxonomy,
            slug: $wpTerm->slug ?: Str::slug($wpTerm->name)
        );

        $term = TaxonomyTerm::query()->create([
            'taxonomy' => $taxonomy,
            'title' => $wpTerm->name,
            'slug' => $slug,
            'path' => $slug,
            'parent_id' => null,
            'description' => $wpTerm->description ?: null,
        ]);

        $this->createOrUpdateCurrentSlug($term);

        WpMigrationMap::query()->create([
            'source_type' => $sourceType,
            'source_id' => $wpTerm->term_taxonomy_id,
            'target_type' => TaxonomyTerm::class,
            'target_id' => $term->id,
            'meta' => [
                'term_id' => $wpTerm->term_id,
                'term_taxonomy_id' => $wpTerm->term_taxonomy_id,
                'parent' => $wpTerm->parent,
                'count' => $wpTerm->count,
                'original_slug' => $wpTerm->slug,
            ],
        ]);
    }

    private function syncCategoryParentsAndPaths(?Closure $progressCallback = null): void
    {
        $categories = DB::connection('wordpress')
            ->table('terms')
            ->join('term_taxonomy', 'terms.term_id', '=', 'term_taxonomy.term_id')
            ->where('term_taxonomy.taxonomy', 'category')
            ->select([
                'terms.term_id',
                'terms.slug',
                'term_taxonomy.term_taxonomy_id',
                'term_taxonomy.parent',
            ])
            ->orderBy('term_taxonomy.parent')
            ->get();

        foreach ($categories as $wpCategory) {
            $childMap = $this->findMap('wordpress_category', $wpCategory->term_taxonomy_id);

            if (!$childMap) {
                $this->advanceProgress($progressCallback);
                continue;
            }

            $term = TaxonomyTerm::query()->find($childMap->target_id);

            if (!$term) {
                $this->advanceProgress($progressCallback);
                continue;
            }

            $parentId = null;

            if ((int)$wpCategory->parent > 0) {
                $parentWpTaxonomy = DB::connection('wordpress')
                    ->table('term_taxonomy')
                    ->where('taxonomy', 'category')
                    ->where('term_id', $wpCategory->parent)
                    ->first();

                if ($parentWpTaxonomy) {
                    $parentMap = $this->findMap('wordpress_category', $parentWpTaxonomy->term_taxonomy_id);

                    if ($parentMap) {
                        $parentId = $parentMap->target_id;
                    }
                }
            }

            $term->update([
                'parent_id' => $parentId,
                'path' => $this->buildCategoryPath($term, $parentId),
            ]);

            $term->refresh();

            $this->createOrUpdateCurrentSlug($term);

            $this->advanceProgress($progressCallback);
        }
    }

    private function createOrUpdateCurrentSlug(TaxonomyTerm $term): void
    {
        TaxonomyTermSlug::query()
            ->where('taxonomy_term_id', $term->id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
            ]);

        TaxonomyTermSlug::query()->updateOrCreate(
            [
                'taxonomy_term_id' => $term->id,
                'route_key' => $term->path,
            ],
            [
                'taxonomy' => $term->taxonomy,
                'is_current' => true,
            ]
        );
    }

    private function buildCategoryPath(TaxonomyTerm $term, ?int $parentId): string
    {
        if (!$parentId) {
            return $term->slug;
        }

        $parent = TaxonomyTerm::query()->find($parentId);

        if (!$parent) {
            return $term->slug;
        }

        return trim($parent->path . '/' . $term->slug, '/');
    }

    private function findMap(string $sourceType, int $sourceId): ?WpMigrationMap
    {
        return WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('target_type', TaxonomyTerm::class)
            ->first();
    }

    private function makeUniqueSlug(TaxonomyKeyEnum $taxonomy, string $slug): string
    {
        $base = $slug ?: 'term';
        $slug = $base;
        $counter = 1;

        while (
        TaxonomyTerm::query()
            ->where('taxonomy', $taxonomy->value)
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function advanceProgress(?Closure $progressCallback): void
    {
        if ($progressCallback) {
            $progressCallback();
        }
    }
}
