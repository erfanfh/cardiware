<?php

namespace App\Application\Content\Services;

use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostCacheInvalidationService
{
    public function forgetAfterPostCreated(Post $post): void
    {
        $this->flushPostCaches($post);
        $this->flushPostListCaches($post);
    }

    public function forgetAfterPostUpdated(Post $post): void
    {
        $this->flushPostCaches($post);
        $this->flushPostListCaches($post);
    }

    public function forgetAfterPostDeleted(Post $post): void
    {
        $this->flushPostCaches($post);
        $this->flushPostListCaches($post);
    }

    protected function flushPostCaches(Post $post): void
    {
        Cache::tags([
            'seo',
            'schema',
            'posts',
            "post:{$post->id}",
        ])->flush();
    }

    protected function flushPostListCaches(Post $post): void
    {
        Cache::tags([
            'home',
            'post-lists',
            'categories',
            'tags',
            'sitemap',
            'feed',
        ])->flush();
    }
}
