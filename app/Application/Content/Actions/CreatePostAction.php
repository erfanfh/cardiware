<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Download\Actions\SyncDownloadSectionsAction;
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
        protected SyncModelTagsAction        $syncModelTagsAction,
        protected SyncModelCategoriesAction  $syncModelCategoriesAction,
        protected SyncPostFeaturesAction     $syncPostFeaturesAction,
        protected AssignSlugAction           $assignSlugAction,
        protected ContentSlugCacheService    $contentSlugCacheService,
        protected SyncDownloadSectionsAction $syncDownloadSectionsAction,
        protected SyncPostImageGalleryAction $syncPostImageGalleryAction,
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
                $postData['category_ids'],
                $postData['slug'],
                $postData['download_sections'],
                $postData['image_gallery_items'],
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
            $this->syncDownloadSectionsAction->execute($post, $data['download_sections'] ?? []);
            $this->syncPostImageGalleryAction->execute($post, $data['image_gallery'] ?? []);

            return $post;
        });

        $this->contentSlugCacheService->forgetForModel($post);

        return $post;
    }
}
