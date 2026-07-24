<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\User\FindUserByMobileAction;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Application\Identity\Exceptions\UserNotFoundException;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;

final class GetActiveUserByMobileAction
{
    public function __construct(
        private readonly NormalizeMobileAction  $normalizeMobileAction,
        private readonly FindUserByMobileAction $findUserByMobileAction,
    )
    {
    }

    public function execute(string $mobile): User
    {
        $mobile = $this->normalizeMobileAction->execute($mobile);

        $user = $this->findUserByMobileAction->execute(
            mobile: $mobile,
            withTrashed: true,
        );

        if (!$user) {
            throw new UserNotFoundException(
                'کاربری با این شماره موبایل یافت نشد.'
            );
        }

        if ($user->trashed()) {
            throw new AccountUnavailableException(
                'حساب مرتبط با این شماره در دسترس نیست. لطفا با پشتیبانی تماس بگیرید.'
            );
        }

        if ($user->status !== UserStatusEnum::ACTIVE) {
            throw new AccountUnavailableException(
                'حساب کاربری شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.'
            );
        }

        return $user;
    }
}
