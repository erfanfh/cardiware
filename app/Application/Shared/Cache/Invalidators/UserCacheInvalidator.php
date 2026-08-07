<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;

class UserCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::USER_ACCESS,
        ])->flush();
    }

    public function flushRoles(): void
    {
        Cache::tags([
            CacheTag::USER_ACCESS,
            CacheTag::ROLES,
        ])->flush();
    }

    public function flushForUser(User|int|string $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Cache::tags([
            CacheTag::userAccess($userId),
            CacheTag::user($userId),
        ])->flush();
    }

    public function flushAccountForUser(User|int|string $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Cache::tags([
            CacheTag::user($userId),
        ])->flush();
    }
}
