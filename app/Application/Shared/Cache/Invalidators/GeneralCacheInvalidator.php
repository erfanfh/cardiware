<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class GeneralCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::POST_DETAIL,
            CacheTag::RELATED_POSTS,
        ])->flush();
    }
}
