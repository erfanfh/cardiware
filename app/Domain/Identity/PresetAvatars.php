<?php

namespace App\Domain\Identity;

final class PresetAvatars
{
    /** @return list<int> */
    public static function ids(): array
    {
        return range(1, 12);
    }

    public static function url(int $id): ?string
    {
        return in_array($id, self::ids(), true)
            ? asset("img/avatars/avatar{$id}.png")
            : null;
    }
}
