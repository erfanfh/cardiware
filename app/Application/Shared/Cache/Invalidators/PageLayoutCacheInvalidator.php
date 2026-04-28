<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Layout\Models\PageLayout;
use Illuminate\Support\Facades\Cache;

class PageLayoutCacheInvalidator
{
    public function flushForLayout(PageLayout $pageLayout): void
    {
        Cache::tags([
            CacheTag::PAGE_LAYOUTS,
            CacheTag::pageLayout($pageLayout->key),
        ])->flush();
    }
}
