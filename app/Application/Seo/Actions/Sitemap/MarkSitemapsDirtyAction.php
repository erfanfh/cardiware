<?php

namespace App\Application\Seo\Actions\Sitemap;

use Illuminate\Support\Facades\Cache;

class MarkSitemapsDirtyAction
{
    public const CACHE_KEY = 'seo:sitemaps:dirty';

    public function execute(): void
    {
        Cache::put(self::CACHE_KEY, true, now()->addHours(6));
    }
}
