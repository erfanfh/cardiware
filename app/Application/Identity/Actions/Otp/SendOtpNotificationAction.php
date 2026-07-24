<?php

namespace App\Application\Identity\Actions\Otp;

use App\Infrastructure\Services\Sms\SmsService;

final class SendOtpNotificationAction
{
    public function __construct(
        private readonly SmsService $smsService,
    )
    {
    }

    public function execute(
        string $mobile,
        string $code,
    ): void
    {
        $this->smsService->sendOtp(
            mobile: $mobile,
            code: $code,
        );
    }
}
