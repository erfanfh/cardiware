<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Services\ContentSlugCacheService;
use App\Domain\Content\Models\Post;

class DeletePostAction
{
    public function __construct(
        protected ContentSlugCacheService $contentSlugCacheService,
    )
    {
    }

    public function execute(Post $post): void
    {
        $this->contentSlugCacheService->forgetForModel($post);

        $post->delete();
    }
}
