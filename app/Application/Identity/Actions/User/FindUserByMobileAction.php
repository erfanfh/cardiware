<?php

namespace App\Application\Identity\Actions\User;

use App\Domain\Identity\Models\User;

final class FindUserByMobileAction
{
    public function execute(
        string $mobile,
        bool   $withTrashed = true,
    ): ?User
    {
        $query = User::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query
            ->where('mobile', $mobile)
            ->first();
    }
}
