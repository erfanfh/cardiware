<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Support\OtpSendLock;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Random\RandomException;
use Throwable;

final class RequestOtpAction
{
    public function __construct(
        private readonly NormalizeMobileAction          $normalizeMobileAction,
        private readonly CheckOtpSendAvailabilityAction $checkOtpSendAvailabilityAction,
        private readonly GenerateOtpCodeAction          $generateOtpCodeAction,
        private readonly CreateOtpAction                $createOtpAction,
        private readonly SendOtpNotificationAction      $sendOtpNotificationAction,
        private readonly InvalidatePreviousOtpsAction   $invalidatePreviousOtpsAction,
        private readonly OtpSendLock                    $otpSendLock,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): SendOtpResultDTO
    {
        $mobile = $this->normalizeMobileAction->execute(
            $mobile
        );

        return $this->otpSendLock->run(
            mobile: $mobile,
            otpType: $otpType,
            callback: fn(): SendOtpResultDTO => $this->request(
                mobile: $mobile,
                otpType: $otpType,
            ),
        );
    }

    /**
     * @throws RandomException
     */
    private function request(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): SendOtpResultDTO
    {
        $availability = $this
            ->checkOtpSendAvailabilityAction
            ->execute(
                mobile: $mobile,
                otpType: $otpType,
            );

        if (!$availability->canSend) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: true,
                message: 'کد تایید قبلا برای شما ارسال شده است.',
                retryAfterSeconds: $availability->retryAfterSeconds,
                expiresAfterSeconds: $availability->expiresAfterSeconds,
            );
        }

        $code = $this->generateOtpCodeAction->execute();

        $otp = $this->createOtpAction->execute(
            mobile: $mobile,
            code: $code,
            otpType: $otpType,
        );

        try {
            $this->sendOtpNotificationAction->execute(
                mobile: $mobile,
                code: $code,
            );
        } catch (Throwable $exception) {
            $otp->delete();

            report($exception);

            return new SendOtpResultDTO(
                sent: false,
                alreadySent: false,
                message: 'ارسال کد تایید با خطا مواجه شد. دوباره تلاش کنید.',
            );
        }

        try {
            $this->invalidatePreviousOtpsAction->execute(
                mobile: $mobile,
                otpType: $otpType,
                exceptOtpId: $otp->getKey(),
            );
        } catch (Throwable $exception) {
            /*
             * پیامک ارسال شده و OTP جدید معتبر است.
             * شکست در باطل کردن رکوردهای قدیمی نباید پاسخ ارسال را ناموفق کند.
             */
            report($exception);
        }

        return new SendOtpResultDTO(
            sent: true,
            alreadySent: false,
            message: 'کد یکبار مصرف با موفقیت ارسال شد.',
            retryAfterSeconds: max(
                30,
                (int)config(
                    'auth-flow.otp.resend_after_seconds',
                    300
                )
            ),
            expiresAfterSeconds: max(
                60,
                (int)config(
                    'auth-flow.otp.expires_after_seconds',
                    300
                )
            ),
        );
    }
}
