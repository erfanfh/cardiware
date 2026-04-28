<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Content\Models\Page;
use Illuminate\Support\Facades\Cache;

class PageCacheInvalidator
{
    public function afterCreated(Page $page): void
    {
        $this->flushPageRelatedCaches($page);
    }

    public function afterUpdated(Page $page): void
    {
        $this->flushPageRelatedCaches($page);
    }

    public function afterDeleted(Page $page): void
    {
        $this->flushPageRelatedCaches($page);
    }

    protected function flushPageRelatedCaches(Page $page): void
    {
        $this->flushPageDetailCaches($page);
        $this->flushPageSeoCaches($page);
        $this->flushPageSlugCaches($page);
    }

    protected function flushPageDetailCaches(Page $page): void
    {
        Cache::tags([
            CacheTag::PAGES,
            CacheTag::PAGE_DETAIL,
            CacheTag::page($page->id),
        ])->flush();
    }

    protected function flushPageSeoCaches(Page $page): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::PAGES,
            CacheTag::page($page->id),
        ])->flush();
    }

    protected function flushPageSlugCaches(Page $page): void
    {
        Cache::tags([
            CacheTag::PAGES,
            CacheTag::page($page->id),
        ])->flush();
    }
}
