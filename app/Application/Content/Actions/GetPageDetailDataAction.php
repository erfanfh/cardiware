<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Page;
use Illuminate\Support\Facades\Cache;

class GetPageDetailDataAction
{
    public function execute(Page $page): Page
    {
        return Cache::tags([
            CacheTag::PAGES,
            CacheTag::PAGE_DETAIL,
            CacheTag::page($page->id),
        ])->remember(
            CacheKey::pageDetailData(
                pageId: $page->id,
                pageUpdatedAt: $page->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $page
                ->load([
                    'picture.primaryVariant',
                    'picture.variants',
                    'author',
                ])
        );
    }
}
