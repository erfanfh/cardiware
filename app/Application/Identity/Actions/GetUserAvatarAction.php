<?php

namespace App\Application\Identity\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;

final class GetUserAvatarAction
{
    public function execute(User $user): array
    {
        return Cache::tags([
            CacheTag::user($user->id),
            CacheTag::ACCOUNT,
        ])->remember(
            CacheKey::userAvatar($user->id),
            CacheTtl::long(),
            fn() => $this->resolve($user)
        );
    }

    private function resolve(User $user): array
    {
        $avatar = $user->avatar;

        return [
            'url' => $avatar?->url,
        ];
    }
}
