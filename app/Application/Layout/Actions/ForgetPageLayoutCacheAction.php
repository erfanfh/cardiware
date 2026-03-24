<?php

namespace App\Application\Layout\Actions;

use App\Application\Layout\Support\PageLayoutCacheKeys;
use App\Domain\Layout\Models\PageLayout;
use Illuminate\Support\Facades\Cache;

class ForgetPageLayoutCacheAction
{
    /**
     * @param PageLayout $pageLayout
     * @return void
     */
    public function execute(PageLayout $pageLayout): void
    {
        Cache::forget(PageLayoutCacheKeys::render($pageLayout->key));
        Cache::forget(PageLayoutCacheKeys::builder($pageLayout->key));
    }
}
