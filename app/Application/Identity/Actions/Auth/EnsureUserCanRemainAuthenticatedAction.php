<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;

final class EnsureUserCanRemainAuthenticatedAction
{
    public function execute(User $user): void
    {
        if ($user->trashed()) {
            throw new AccountUnavailableException(
                'حساب کاربری شما در دسترس نیست.'
            );
        }

        if ($user->status !== UserStatusEnum::ACTIVE) {
            throw new AccountUnavailableException(
                'حساب کاربری شما غیرفعال شده است.'
            );
        }
    }
}
