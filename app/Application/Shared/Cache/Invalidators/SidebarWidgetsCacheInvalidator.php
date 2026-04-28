<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class SidebarWidgetsCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_CATEGORIES,
            CacheTag::SIDEBAR_COMMENTS,
            CacheTag::SIDEBAR_PROJECTS,
        ])->flush();
    }
}
