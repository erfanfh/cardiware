<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Shared\Cache\Invalidators\PostCacheInvalidator;
use App\Domain\Content\Models\Post;

class DeletePostAction
{
    public function __construct(
        protected ContentSlugCacheService $contentSlugCacheService,
        protected PostCacheInvalidator    $postCacheInvalidator,
    )
    {
    }

    public function execute(Post $post): void
    {
        $this->contentSlugCacheService->forgetForModel($post);
        $this->postCacheInvalidator->afterDeleted($post);

        $post->delete();
    }
}
