<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use Illuminate\Support\Facades\Cache;

class UserAccessCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::USER_ACCESS,
            CacheTag::ROLES,
        ])->flush();
    }

    public function flushRoles(): void
    {
        Cache::tags([
            CacheTag::ROLES,
        ])->flush();
    }
}
