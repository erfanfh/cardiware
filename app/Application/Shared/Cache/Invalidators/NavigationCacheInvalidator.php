<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Support\Facades\Cache;

class NavigationCacheInvalidator
{
    public function flushAll(): void
    {
        Cache::tags([
            CacheTag::NAVIGATION,
        ])->flush();
    }

    public function flushForMenu(NavigationMenu|string $menu): void
    {
        $slug = $menu instanceof NavigationMenu ? $menu->slug : $menu;

        Cache::tags([
            CacheTag::NAVIGATION,
            CacheTag::navigationMenu($slug),
        ])->flush();
    }
}
