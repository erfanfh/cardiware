<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class FooterCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::FOOTER_STATS,
        ])->flush();
    }

    public function flushStats(): void
    {
        Cache::tags([
            CacheTag::FOOTER_STATS,
        ])->flush();
    }
}
