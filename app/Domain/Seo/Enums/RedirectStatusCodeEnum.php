<?php

namespace App\Domain\Seo\Enums;

enum RedirectStatusCodeEnum: int
{
    case MovedPermanently = 301;
    case Found = 302;
    case TemporaryRedirect = 307;
    case PermanentRedirect = 308;
    case Gone = 410;

    public function isRedirect(): bool
    {
        return in_array($this, [
            self::MovedPermanently,
            self::Found,
            self::TemporaryRedirect,
            self::PermanentRedirect,
        ], true);
    }

    public function isGone(): bool
    {
        return $this === self::Gone;
    }
}
