<?php

namespace App\Application\Identity\Actions\Otp;

use App\Domain\Identity\Models\Otp;

final class MarkOtpAsUsedAction
{
    public function execute(Otp $otp): void
    {
        $otp->forceFill([
            'is_used' => true,
        ])->save();
    }
}
