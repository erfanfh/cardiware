<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostTaxonomiesImporter
{
    private int $processed = 0;
    private int $attached = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('term_relationships')
            ->join('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
            ->whereIn('term_taxonomy.taxonomy', ['category', 'post_tag'])
            ->orderBy('term_relationships.object_id')
            ->select([
                'term_relationships.object_id',
                'term_relationships.term_taxonomy_id',
                'term_taxonomy.taxonomy',
            ])
            ->chunk(1000, function ($relations) use ($onProgress) {
                foreach ($relations as $relation) {
                    $this->processed++;

                    try {
                        $result = $this->importRelation($relation);

                        if ($result === 'attached') {
                            $this->attached++;
                        }

                        if ($result === 'skipped') {
                            $this->skipped++;
                        }
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress post taxonomy relation import failed', [
                            'wordpress_object_id' => $relation->object_id,
                            'wordpress_term_taxonomy_id' => $relation->term_taxonomy_id,
                            'taxonomy' => $relation->taxonomy,
                            'message' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                            'trace' => $exception->getTraceAsString(),
                        ]);
                    }

                    if ($onProgress) {
                        $onProgress($this->stats());
                    }
                }
            });
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('term_relationships')
            ->join('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
            ->whereIn('term_taxonomy.taxonomy', ['category', 'post_tag'])
            ->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'attached' => $this->attached,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }

    private function importRelation(object $relation): string
    {
        $postMap = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $relation->object_id)
            ->where('target_type', Post::class)
            ->first();

        if (!$postMap) {
            logger()->warning('WordPress post taxonomy relation skipped because post map was not found', [
                'wordpress_object_id' => $relation->object_id,
                'wordpress_term_taxonomy_id' => $relation->term_taxonomy_id,
                'taxonomy' => $relation->taxonomy,
            ]);

            return 'skipped';
        }

        $sourceType = $relation->taxonomy === 'category'
            ? 'wordpress_category'
            : 'wordpress_tag';

        $termMap = WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $relation->term_taxonomy_id)
            ->where('target_type', TaxonomyTerm::class)
            ->first();

        if (!$termMap) {
            logger()->warning('WordPress post taxonomy relation skipped because taxonomy term map was not found', [
                'wordpress_object_id' => $relation->object_id,
                'wordpress_term_taxonomy_id' => $relation->term_taxonomy_id,
                'taxonomy' => $relation->taxonomy,
                'expected_source_type' => $sourceType,
            ]);

            return 'skipped';
        }

        DB::table('taxonomables')->updateOrInsert(
            [
                'taxonomy_term_id' => $termMap->target_id,
                'taxonomable_type' => Post::class,
                'taxonomable_id' => $postMap->target_id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return 'attached';
    }
}
