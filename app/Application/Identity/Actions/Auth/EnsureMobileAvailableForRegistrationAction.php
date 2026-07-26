<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\User\FindUserByMobileAction;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Application\Identity\Exceptions\MobileAlreadyRegisteredException;

final class EnsureMobileAvailableForRegistrationAction
{
    public function __construct(
        private readonly NormalizeMobileAction  $normalizeMobileAction,
        private readonly FindUserByMobileAction $findUserByMobileAction,
    )
    {
    }

    public function execute(string $mobile): string
    {
        $mobile = $this->normalizeMobileAction->execute($mobile);

        $user = $this->findUserByMobileAction->execute(
            mobile: $mobile,
            withTrashed: true,
        );

        if (!$user) {
            return $mobile;
        }

        if ($user->trashed()) {
            throw new AccountUnavailableException(
                'حساب مرتبط با این شماره قبلا ایجاد شده است. لطفا با پشتیبانی تماس بگیرید.'
            );
        }

        throw new MobileAlreadyRegisteredException(
            'این شماره موبایل قبلا ثبت شده است.'
        );
    }
}
