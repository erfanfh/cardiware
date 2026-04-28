<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostCacheInvalidator
{
    public function __construct(
        protected SeoCacheInvalidator    $seoCacheInvalidator,
        protected HomeCacheInvalidator   $homeCacheInvalidator,
        protected FooterCacheInvalidator $footerCacheInvalidator,
    )
    {
    }

    public function afterCreated(Post $post): void
    {
        $this->flushPostRelatedCaches($post);
    }

    public function afterUpdated(Post $post): void
    {
        $this->flushPostRelatedCaches($post);
    }

    public function afterDeleted(Post $post): void
    {
        $this->flushPostRelatedCaches($post);
    }

    protected function flushPostRelatedCaches(Post $post): void
    {
        $this->seoCacheInvalidator->flushForPost($post);
        $this->homeCacheInvalidator->flushAll();
        $this->footerCacheInvalidator->flushStats();

        Cache::tags([
            CacheTag::POSTS,
            CacheTag::POST_LISTS,
            CacheTag::CATEGORIES,
            CacheTag::TAGS,
            CacheTag::SITEMAP,
        ])->flush();
    }
}
