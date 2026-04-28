<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\Invalidators\ContentSlugCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\PageCacheInvalidator;
use App\Domain\Content\Models\Page;

class DeletePageAction
{
    public function __construct(
        protected ContentSlugCacheInvalidator $contentSlugCacheInvalidator,
        protected PageCacheInvalidator        $pageCacheInvalidator,
    )
    {
    }

    public function execute(Page $page): void
    {
        $this->contentSlugCacheInvalidator->forgetForModel($page);
        $this->pageCacheInvalidator->afterDeleted($page);

        $page->delete();
    }
}
