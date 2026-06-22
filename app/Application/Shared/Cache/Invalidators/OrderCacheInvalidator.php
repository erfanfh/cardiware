<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class OrderCacheInvalidator
{
    public function flushSuccessfulPayment(): void
    {
        Cache::tags([
            CacheTag::STORE_STATS,
        ])->flush();
    }
}
