<?php

namespace App\Application\Identity\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Identity\Models\Role;
use Illuminate\Support\Facades\Cache;

class GetDefaultRoleAction
{
    public function execute(): Role
    {
        return Cache::tags([
            CacheTag::USER_ACCESS,
            CacheTag::ROLES,
        ])->remember(
            CacheKey::defaultRole(),
            CacheTtl::long(),
            fn() => Role::query()
                ->where('is_default', true)
                ->firstOrFail()
        );
    }
}
