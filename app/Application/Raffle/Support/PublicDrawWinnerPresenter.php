<?php

namespace App\Application\Raffle\Support;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Str;

class PublicDrawWinnerPresenter
{
    public function maskedName(User $user): string
    {
        $firstName = trim((string)$user->first_name);
        $lastName = trim((string)$user->last_name);

        if ($firstName !== '' || $lastName !== '') {
            $maskedLastName = $lastName !== ''
                ? Str::substr($lastName, 0, 1) . '***'
                : '';

            return trim($firstName . ' ' . $maskedLastName);
        }

        $displayName = trim((string)$user->display_name);

        if ($displayName === '') {
            return 'کاربر Cardiware';
        }

        return Str::substr($displayName, 0, 2) . '***';
    }

    public function maskedTicketCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $length = Str::length($code);

        if ($length <= 6) {
            return Str::substr($code, 0, 2) . '***';
        }

        return Str::substr($code, 0, 4)
            . '-***-'
            . Str::substr($code, -4);
    }
}
