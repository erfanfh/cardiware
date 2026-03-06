<?php

namespace App\Domain\Content\Actions;

use App\Domain\Audit\Actions\SyncRelationsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Support\Facades\DB;

/**
 * Action responsible for updating a Post and
 * synchronizing its related taxonomy terms.
 *
 * The entire process runs inside a database transaction
 * to guarantee atomic updates and consistent audit logs.
 */
class UpdatePostAction
{
    public function __construct(
        protected SyncRelationsAction $syncRelation,
    )
    {
    }

    /**
     * Execute the post update workflow.
     *
     * @param array<string, mixed> $data
     * @param Post $post
     * @return Post
     */
    public function execute(array $data, Post $post): Post
    {
        return DB::transaction(function () use ($data, $post) {

            $post->update($data);

            // sync categories
            $this->syncRelation->execute(
                model: $post,
                relation: 'categories',
                items: $data['categories'],
                pivotBuilder: fn($id) => [
                    'type' => TaxonomyTermTypeEnum::CATEGORY->value,
                ],
                auditRelationName: 'taxonomy_terms',
                foreignKeyName: 'taxonomy_term_id',
                auditMeta: ['type' => 'category']
            );


            $tagIds = $post->resolveTagIds($data['tags']);

            // sync tags
            $this->syncRelation->execute(
                model: $post,
                relation: 'tags',
                items: $tagIds,
                pivotBuilder: fn($id) => [
                    'type' => TaxonomyTermTypeEnum::TAG->value,
                ],
                auditRelationName: 'taxonomy_terms',
                foreignKeyName: 'taxonomy_term_id',
                auditMeta: ['type' => 'tag']
            );

            return $post;
        });
    }
}
