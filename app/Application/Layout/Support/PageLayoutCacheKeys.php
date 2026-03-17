<?php

namespace App\Application\Layout\Support;

class PageLayoutCacheKeys
{
    /**
     * @param string $layoutKey
     * @return string
     */
    public static function render(string $layoutKey): string
    {
        return "page_layout:render:$layoutKey";
    }

    /**
     * @param string $layoutKey
     * @return string
     */
    public static function builder(string $layoutKey): string
    {
        return "page_layout:builder:$layoutKey";
    }
}
