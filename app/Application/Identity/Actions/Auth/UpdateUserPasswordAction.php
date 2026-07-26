<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;

final class UpdateUserPasswordAction
{
    public function execute(
        User   $user,
        string $password,
    ): User
    {
        /*
         * مدل User از cast نوع hashed استفاده می‌کند.
         * بنابراین نباید اینجا Hash::make فراخوانی شود.
         */
        $user->forceFill([
            'password' => $password,
        ])->save();

        return $user->refresh();
    }
}
