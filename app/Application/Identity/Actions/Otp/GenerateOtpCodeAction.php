<?php

namespace App\Application\Identity\Actions\Otp;

use Random\RandomException;

final class GenerateOtpCodeAction
{
    /**
     * @throws RandomException
     */
    public function execute(): string
    {
        $length = min(
            8,
            max(
                4,
                (int)config('auth-flow.otp.length', 6)
            )
        );

        $minimum = 10 ** ($length - 1);
        $maximum = (10 ** $length) - 1;

        return (string)random_int(
            $minimum,
            $maximum
        );
    }
}
