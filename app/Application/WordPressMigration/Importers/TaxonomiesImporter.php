<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TaxonomiesImporter
{
    private int $processed = 0;
    private int $created = 0;
    private int $synced = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function count(): int
    {
        return ($this->countTaxonomy('category') * 2)
            + $this->countTaxonomy('post_tag');
    }

    public function import(?Closure $progressCallback = null): void
    {
        $this->importCategories($progressCallback);
        $this->syncCategoryParentsAndPaths($progressCallback);
        $this->importTags($progressCallback);
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'created' => $this->created,
            'synced' => $this->synced,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
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
                    $this->processed++;

                    try {
                        $result = $this->importTerm($wpTerm, $sourceType, $taxonomy);

                        if ($result === 'created') {
                            $this->created++;
                        }

                        if ($result === 'skipped') {
                            $this->skipped++;
                        }
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress taxonomy term import failed', [
                            'source_type' => $sourceType,
                            'wordpress_term_id' => $wpTerm->term_id,
                            'wordpress_term_taxonomy_id' => $wpTerm->term_taxonomy_id,
                            'name' => $wpTerm->name ?? null,
                            'slug' => $wpTerm->slug ?? null,
                            'message' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                            'trace' => $exception->getTraceAsString(),
                        ]);
                    }

                    $this->advanceProgress($progressCallback);
                }
            });
    }

    private function importTerm(object $wpTerm, string $sourceType, TaxonomyKeyEnum $taxonomy): string
    {
        $existingMap = WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $wpTerm->term_taxonomy_id)
            ->where('target_type', TaxonomyTerm::class)
            ->first();

        if ($existingMap) {
            $term = TaxonomyTerm::query()->find($existingMap->target_id);

            if (!$term) {
                logger()->warning('WordPress taxonomy term skipped because target taxonomy term was not found', [
                    'source_type' => $sourceType,
                    'wordpress_term_id' => $wpTerm->term_id,
                    'wordpress_term_taxonomy_id' => $wpTerm->term_taxonomy_id,
                    'target_taxonomy_term_id' => $existingMap->target_id,
                    'name' => $wpTerm->name ?? null,
                    'slug' => $wpTerm->slug ?? null,
                ]);

                return 'skipped';
            }

            $term->update([
                'title' => $this->cleanText($wpTerm->name),
                'description' => $wpTerm->description ?: null,
                'slug' => $this->normalizeSlug($term->slug),
                'path' => $this->normalizePath($term->path),
            ]);

            $this->createOrUpdateCurrentSlug($term->refresh());

            return 'skipped';
        }

        $slug = $this->makeUniqueSlug(
            taxonomy: $taxonomy,
            slug: $this->normalizeSlug($wpTerm->slug)
                ?: Str::slug($wpTerm->name)
                    ?: 'term-' . $wpTerm->term_taxonomy_id
        );

        $term = TaxonomyTerm::query()->create([
            'taxonomy' => $taxonomy,
            'title' => $this->cleanText($wpTerm->name) ?: 'Untitled Term ' . $wpTerm->term_taxonomy_id,
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
                'normalized_slug' => $slug,
            ],
        ]);

        return 'created';
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
            $this->processed++;

            try {
                $result = $this->syncCategoryParentAndPath($wpCategory);

                if ($result === 'synced') {
                    $this->synced++;
                }

                if ($result === 'skipped') {
                    $this->skipped++;
                }
            } catch (Throwable $exception) {
                $this->failed++;

                logger()->error('WordPress category parent/path sync failed', [
                    'wordpress_term_id' => $wpCategory->term_id,
                    'wordpress_term_taxonomy_id' => $wpCategory->term_taxonomy_id,
                    'parent' => $wpCategory->parent ?? null,
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }

            $this->advanceProgress($progressCallback);
        }
    }

    private function syncCategoryParentAndPath(object $wpCategory): string
    {
        $childMap = $this->findMap('wordpress_category', $wpCategory->term_taxonomy_id);

        if (!$childMap) {
            logger()->warning('WordPress category parent/path sync skipped because category map was not found', [
                'wordpress_term_id' => $wpCategory->term_id,
                'wordpress_term_taxonomy_id' => $wpCategory->term_taxonomy_id,
                'parent' => $wpCategory->parent,
            ]);

            return 'skipped';
        }

        $term = TaxonomyTerm::query()->find($childMap->target_id);

        if (!$term) {
            logger()->warning('WordPress category parent/path sync skipped because target taxonomy term was not found', [
                'wordpress_term_id' => $wpCategory->term_id,
                'wordpress_term_taxonomy_id' => $wpCategory->term_taxonomy_id,
                'target_taxonomy_term_id' => $childMap->target_id,
            ]);

            return 'skipped';
        }

        $parentId = null;

        if ((int)$wpCategory->parent > 0) {
            $parentWpTaxonomy = DB::connection('wordpress')
                ->table('term_taxonomy')
                ->where('taxonomy', 'category')
                ->where('term_id', $wpCategory->parent)
                ->first();

            if (!$parentWpTaxonomy) {
                logger()->warning('WordPress category parent taxonomy was not found', [
                    'wordpress_term_taxonomy_id' => $wpCategory->term_taxonomy_id,
                    'wordpress_parent_term_id' => $wpCategory->parent,
                ]);
            } else {
                $parentMap = $this->findMap('wordpress_category', $parentWpTaxonomy->term_taxonomy_id);

                if ($parentMap) {
                    $parentId = $parentMap->target_id;
                } else {
                    logger()->warning('WordPress category parent map was not found', [
                        'wordpress_term_taxonomy_id' => $wpCategory->term_taxonomy_id,
                        'wordpress_parent_term_id' => $wpCategory->parent,
                        'wordpress_parent_term_taxonomy_id' => $parentWpTaxonomy->term_taxonomy_id,
                    ]);
                }
            }
        }

        $term->update([
            'slug' => $this->normalizeSlug($term->slug),
            'parent_id' => $parentId,
            'path' => $this->buildCategoryPath($term, $parentId),
        ]);

        $term->refresh();

        $this->createOrUpdateCurrentSlug($term);

        return 'synced';
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
                'route_key' => $this->normalizePath($term->path),
            ],
            [
                'taxonomy' => $term->taxonomy,
                'is_current' => true,
            ]
        );
    }

    private function buildCategoryPath(TaxonomyTerm $term, ?int $parentId): string
    {
        $slug = $this->normalizeSlug($term->slug);

        if (!$parentId) {
            return $slug;
        }

        $parent = TaxonomyTerm::query()->find($parentId);

        if (!$parent) {
            return $slug;
        }

        return $this->normalizePath($parent->path . '/' . $slug);
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
        $base = $this->normalizeSlug($slug) ?: 'term';
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

    private function normalizeSlug(?string $slug): string
    {
        $slug = trim((string)$slug);

        if ($slug === '') {
            return '';
        }

        return trim(rawurldecode($slug));
    }

    private function normalizePath(?string $path): string
    {
        $path = trim((string)$path, '/');

        if ($path === '') {
            return '';
        }

        return collect(explode('/', $path))
            ->map(fn(string $segment) => $this->normalizeSlug($segment))
            ->filter()
            ->implode('/');
    }

    private function cleanText(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function advanceProgress(?Closure $progressCallback): void
    {
        if ($progressCallback) {
            $progressCallback($this->stats());
        }
    }
}
