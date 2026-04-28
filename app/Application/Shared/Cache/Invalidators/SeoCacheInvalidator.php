<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

class SeoCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
        ])->flush();
    }

    public function flushForPost(Post $post): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::POSTS,
            CacheTag::post($post->id),
        ])->flush();
    }
}
