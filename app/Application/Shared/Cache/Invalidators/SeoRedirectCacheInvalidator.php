<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SeoRedirectCacheInvalidator
{
    public function invalidate(): void
    {
        Cache::forever(
            CacheKey::seoRedirectsVersion(),
            (string) Str::uuid()
        );
    }
}
