<?php

namespace App\Application\Identity\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\PresetAvatars;
use Illuminate\Support\Facades\Cache;

final class GetUserAvatarAction
{
    public function execute(User $user): array
    {
        if ($user->preset_avatar !== null && $url = PresetAvatars::url($user->preset_avatar)) {
            return ['url' => $url];
        }

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
        return [
            'url' => $user->avatar?->url,
        ];
    }
}
