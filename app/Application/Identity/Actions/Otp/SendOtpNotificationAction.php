<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\Jobs\SendOtpSmsJob;

final class SendOtpNotificationAction
{
    public function execute(
        string $mobile,
        string $code,
    ): void
    {
        SendOtpSmsJob::dispatch(
            mobile: $mobile,
            code: $code,
        )->afterCommit();
    }
}
