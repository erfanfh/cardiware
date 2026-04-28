<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Content\Services\PostCacheInvalidationService;
use App\Domain\Content\Models\Post;

class DeletePostAction
{
    public function __construct(
        protected ContentSlugCacheService      $contentSlugCacheService,
        protected PostCacheInvalidationService $postCacheInvalidationService,
    )
    {
    }

    public function execute(Post $post): void
    {
        $this->contentSlugCacheService->forgetForModel($post);
        $this->postCacheInvalidationService->forgetAfterPostDeleted($post);

        $post->delete();
    }
}
