<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\Invalidators\ContentSlugCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\PostCacheInvalidator;
use App\Domain\Content\Models\Post;

class DeletePostAction
{
    public function __construct(
        protected ContentSlugCacheInvalidator $contentSlugCacheInvalidator,
        protected PostCacheInvalidator        $postCacheInvalidator,
    )
    {
    }

    public function execute(Post $post): void
    {
        $this->contentSlugCacheInvalidator->forgetForModel($post);
        $this->postCacheInvalidator->afterDeleted($post);

        $post->delete();
    }
}
