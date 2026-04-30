<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomiesImporter
{
    public function import(): void
    {
        $this->importCategories();
        $this->syncCategoryParentsAndPaths();

        $this->importTags();
    }

    private function importCategories(): void
    {
        $this->importTaxonomy(
            wpTaxonomy: 'category',
            sourceType: 'wordpress_category',
            taxonomy: TaxonomyKeyEnum::POST_CATEGORY
        );
    }

    private function importTags(): void
    {
        $this->importTaxonomy(
            wpTaxonomy: 'post_tag',
            sourceType: 'wordpress_tag',
            taxonomy: TaxonomyKeyEnum::POST_TAG
        );
    }

    private function importTaxonomy(string $wpTaxonomy, string $sourceType, TaxonomyKeyEnum $taxonomy): void
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
            ->chunk(500, function ($terms) use ($sourceType, $taxonomy) {
                foreach ($terms as $wpTerm) {
                    $this->importTerm($wpTerm, $sourceType, $taxonomy);
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

            // برای tag همین slug کافیه.
            // برای categoryهای parent دار بعداً sync میشه.
            'path' => $slug,

            'parent_id' => null,
            'description' => $wpTerm->description ?: null,
        ]);

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

    private function syncCategoryParentsAndPaths(): void
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
                continue;
            }

            $term = TaxonomyTerm::query()->find($childMap->target_id);

            if (!$term) {
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
        }
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
}
