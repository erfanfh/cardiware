<?php

namespace App\Application\Identity\Jobs;

use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $mobile,
        public readonly string $code,
    )
    {
    }

    public function handle(SmsService $smsService): void
    {
        $smsService->sendOtp(
            mobile: $this->mobile,
            code: $this->code,
        );
    }

    /**
     * فاصله تلاش‌های مجدد برحسب ثانیه
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }
}
