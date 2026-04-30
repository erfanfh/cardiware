<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;

class PostTaxonomiesImporter
{
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
                    $this->importRelation($relation);

                    if ($onProgress) {
                        $onProgress();
                    }
                }
            });
    }

    private function importRelation(object $relation): void
    {
        $postMap = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $relation->object_id)
            ->where('target_type', Post::class)
            ->first();

        if (!$postMap) {
            return;
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
            return;
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
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('term_relationships')
            ->join('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
            ->whereIn('term_taxonomy.taxonomy', ['category', 'post_tag'])
            ->count();
    }
}
