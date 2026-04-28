<?php

namespace App\Application\Shared\Cache\Invalidators;

class SettingCacheInvalidator
{
    public function __construct(
        protected HomeCacheInvalidator           $homeCacheInvalidator,
        protected SeoCacheInvalidator            $seoCacheInvalidator,
        protected FooterCacheInvalidator         $footerCacheInvalidator,
        protected GeneralCacheInvalidator        $generalCacheInvalidator,
        protected SidebarWidgetsCacheInvalidator $sidebarWidgetsCacheInvalidator,
    )
    {
    }

    public function afterUpdated(string $group, string $key): void
    {
        match ($group) {
            'home' => $this->homeCacheInvalidator->flushAll(),
            'seo' => $this->seoCacheInvalidator->flushAll(),
            'footer' => $this->footerCacheInvalidator->flushAll(),
            'general' => $this->generalCacheInvalidator->flushAll(),
            'sidebarWidgets' => $this->sidebarWidgetsCacheInvalidator->flushAll(),
            default => null,
        };
    }
}
