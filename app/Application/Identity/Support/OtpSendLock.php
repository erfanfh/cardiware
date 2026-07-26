<?php

namespace App\Application\Identity\Support;

use App\Application\Identity\Exceptions\OtpRequestInProgressException;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Closure;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class OtpSendLock
{
    public function run(
        string      $mobile,
        OtpTypeEnum $otpType,
        Closure     $callback,
    ): mixed
    {
        $lock = Cache::lock(
            $this->key($mobile, $otpType),
            10
        );

        if (!$lock->get()) {
            throw new OtpRequestInProgressException(
                'درخواست ارسال کد در حال پردازش است.'
            );
        }

        try {
            return $callback();
        } finally {
            $this->release($lock);
        }
    }

    private function key(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): string
    {
        return 'otp:send:' . hash(
                'sha256',
                $mobile . '|' . $otpType->value
            );
    }

    private function release(Lock $lock): void
    {
        try {
            $lock->release();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
