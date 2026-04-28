<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
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
}
