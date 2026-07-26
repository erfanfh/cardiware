<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\User\FindUserByMobileAction;
use App\Application\Identity\DTOs\ResolveAuthMobileResultDTO;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Domain\Identity\Enums\UserStatusEnum;

final class ResolveAuthMobileAction
{
    public function __construct(
        private readonly NormalizeMobileAction  $normalizeMobileAction,
        private readonly FindUserByMobileAction $findUserByMobileAction,
    )
    {
    }

    public function execute(
        string $mobile,
    ): ResolveAuthMobileResultDTO
    {
        $mobile = $this->normalizeMobileAction->execute(
            $mobile
        );

        $user = $this->findUserByMobileAction->execute(
            mobile: $mobile,
            withTrashed: true,
        );

        if (!$user) {
            return new ResolveAuthMobileResultDTO(
                mobile: $mobile,
                userExists: false,
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

        return new ResolveAuthMobileResultDTO(
            mobile: $mobile,
            userExists: true,
        );
    }
}
