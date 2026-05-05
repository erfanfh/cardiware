<?php

namespace App\Application\Content\Actions;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetPostDetailDataAction
{
    public function __construct(
        protected GetPostFeaturesAction         $getPostFeaturesAction,
        protected GetPageLayoutRenderDataAction $getLayoutRenderDataAction,
    )
    {
    }

    /**
     * @return array{
     *     layoutData: array<string, mixed>,
     *     post: Post,
     *     features: mixed,
     *     relatedPosts: Collection<int, Post>
     * }
     */
    public function execute(Post $post): array
    {
        $updatedAt = $post->updated_at?->timestamp ?? 0;

        return Cache::tags([
            CacheTag::POSTS,
            CacheTag::POST_DETAIL,
            CacheTag::post($post->id),
        ])->remember(
            CacheKey::postDetailData($post->id, $updatedAt),
            CacheTtl::long(),
            fn() => [
                'layoutData' => $this->getLayoutRenderDataAction->execute('post-content'),
                'post' => $this->loadPostForDetail($post),
                'features' => $this->getPostFeaturesAction->execute($post),
                'relatedPosts' => $this->resolveRelatedPosts($post),
            ]
        );
    }

    protected function loadPostForDetail(Post $post): Post
    {
        $post->load([
            'author:id,username,display_name',
            'picture.primaryVariant',
            'picture.variants',
            'categories:id,title,path',
            'tags:id,title,path',
            'imageGallery.mediaFile.primaryVariant',
            'imageGallery.mediaFile.variants',
            'downloadSections.entries',
            'informationBoxes',
        ]);

        $post->loadCount('approvedComments');

        return $post;
    }

    protected function resolveRelatedPosts(Post $post): Collection
    {
        return Cache::tags([
            CacheTag::POSTS,
            CacheTag::RELATED_POSTS,
            CacheTag::post($post->id),
        ])->remember(
            CacheKey::postRelated($post->id, $post->updated_at?->timestamp ?? 0),
            CacheTtl::medium(),
            function () use ($post) {
                $selectionMode = get_setting('general', 'select_related_content_settings');

                $query = Post::query()
                    ->whereKeyNot($post->getKey());

                if ($selectionMode === 'tag') {
                    $tagIds = $post->tags->pluck('id')->filter()->values();

                    if ($tagIds->isNotEmpty()) {
                        $query->whereHas('tags', function ($builder) use ($tagIds) {
                            $builder->whereIn('taxonomy_terms.id', $tagIds);
                        });
                    }
                } elseif ($selectionMode === 'category') {
                    $categoryIds = $post->categories->pluck('id')->filter()->values();

                    if ($categoryIds->isNotEmpty()) {
                        $query->whereHas('categories', function ($builder) use ($categoryIds) {
                            $builder->whereIn('taxonomy_terms.id', $categoryIds);
                        });
                    }
                }

                return $query
                    ->with([
                        'picture.primaryVariant',
                        'picture.variants',
                        'categories:id,title,path',
                        'tags:id,title,path'
                    ])
                    ->latest('published_at')
                    ->take(3)
                    ->get();
            }
        );
    }
}
