<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class HomeCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::HOME,
            CacheTag::HOME_FREE_POSTS,
            CacheTag::HOME_VIDEOS,
            CacheTag::HOME_PROJECTS,
        ])->flush();
    }
}
