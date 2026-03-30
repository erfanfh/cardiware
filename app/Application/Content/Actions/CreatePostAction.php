<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelCategoriesAction;
use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePostAction
{
    public function __construct(
        protected SyncModelTagsAction       $syncModelTagsAction,
        protected SyncModelCategoriesAction $syncModelCategoriesAction,
        protected SyncPostFeaturesAction    $syncPostFeaturesAction,
        protected AssignSlugAction          $assignSlugAction,
        protected ContentSlugCacheService   $contentSlugCacheService,
    )
    {
    }

    /**
     * Create a new post, assign its slug, and sync related data.
     *
     * @param array $data
     * @return Post
     * @throws Throwable
     */
    public function execute(array $data): Post
    {
        $post = DB::transaction(function () use ($data) {
            $postData = $data;

            unset(
                $postData['tags'],
                $postData['slug'],
            );

            $post = Post::create($postData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::CONTENT,
                    model: $post,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelTagsAction->execute($post, $data['tags'] ?? []);
            $this->syncModelCategoriesAction->execute($post, $data['category_ids'] ?? []);
            $this->syncPostFeaturesAction->execute($post, $data);

            return $post;
        });

        $this->contentSlugCacheService->forgetForModel($post);

        return $post;
    }
}
