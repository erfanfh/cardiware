<?php

namespace App\Domain\Content\Actions;

use App\Domain\Audit\Actions\SyncRelationsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Support\Facades\DB;

/**
 * Action responsible for creating a Post along with
 * synchronizing its related taxonomy terms (categories and tags).
 *
 * All operations are executed inside a database transaction
 * to ensure data consistency and proper audit logging.
 */
class CreatePostAction
{
    public function __construct(
        protected SyncRelationsAction $syncRelation,
    )
    {
    }

    /**
     * Execute the post creation workflow.
     *
     * @param array<string, mixed> $data
     * @return Post
     */
    public function execute(array $data): Post
    {
        return DB::transaction(function () use ($data) {

            $post = Post::create($data);

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
