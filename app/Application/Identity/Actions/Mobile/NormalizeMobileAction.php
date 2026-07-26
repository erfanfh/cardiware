<?php

namespace App\Application\Identity\Actions\Mobile;

use App\Application\Identity\Exceptions\InvalidMobileException;

final class NormalizeMobileAction
{
    public function execute(string $mobile): string
    {
        $mobile = normalize_number(trim($mobile));

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            throw new InvalidMobileException(
                'شماره موبایل وارد شده معتبر نیست.'
            );
        }

        return $mobile;
    }
}
