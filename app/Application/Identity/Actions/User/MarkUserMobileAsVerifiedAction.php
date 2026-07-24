<?php

namespace App\Application\Identity\Actions\User;

use App\Domain\Identity\Models\User;

final class MarkUserMobileAsVerifiedAction
{
    public function execute(User $user): User
    {
        if ($user->mobile_verified_at) {
            return $user;
        }

        $user->forceFill([
            'mobile_verified_at' => now(),
        ])->save();

        return $user->refresh();
    }
}
