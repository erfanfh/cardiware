<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Download\Actions\SyncDownloadSectionsAction;
use App\Application\Seo\Actions\Analysis\BuildPostSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Shared\Cache\Invalidators\PostCacheInvalidator;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelPostCategoriesAction;
use App\Application\Taxonomy\Actions\SyncModelPostTagsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePostAction
{
    public function __construct(
        protected SyncModelPostTagsAction       $syncModelPostTagsAction,
        protected SyncModelPostCategoriesAction $syncModelPostCategoriesAction,
        protected SyncPostFeaturesAction        $syncPostFeaturesAction,
        protected AssignSlugAction              $assignSlugAction,
        protected ContentSlugCacheService       $contentSlugCacheService,
        protected SyncDownloadSectionsAction    $syncDownloadSectionsAction,
        protected SyncPostImageGalleryAction    $syncPostImageGalleryAction,
        protected SyncSeoMetaAction             $syncSeoMetaAction,
        protected StoreSeoAnalysisAction        $storeSeoAnalysisAction,
        protected PostCacheInvalidator          $postCacheInvalidator,
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
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $post = DB::transaction(function () use ($data) {
            $postData = $data;

            unset(
                $postData['tags'],
                $postData['category_ids'],
                $postData['slug'],
                $postData['download_sections'],
                $postData['image_gallery_items'],
                $postData['seo'],
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

            $this->syncModelPostTagsAction->execute($post, $data['tags'] ?? []);
            $this->syncModelPostCategoriesAction->execute($post, $data['category_ids'] ?? []);
            $this->syncPostFeaturesAction->execute($post, $data);
            $this->syncDownloadSectionsAction->execute($post, $data['download_sections'] ?? []);
            $this->syncPostImageGalleryAction->execute($post, $data['image_gallery'] ?? []);

            $this->syncSeoMetaAction->execute($post, $data['seo'] ?? []);
            $this->storeSeoAnalysisAction->execute($post, app(BuildPostSeoAnalysisInputAction::class));

            return $post;
        });

        $this->contentSlugCacheService->forgetForModel($post);
        $this->postCacheInvalidator->afterCreated($post);

        return $post;
    }
}
