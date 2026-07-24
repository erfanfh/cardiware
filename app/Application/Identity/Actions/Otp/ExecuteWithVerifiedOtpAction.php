<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Exceptions\InvalidOtpException;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use Closure;
use Illuminate\Support\Facades\DB;

final class ExecuteWithVerifiedOtpAction
{
    public function __construct(
        private readonly NormalizeMobileAction           $normalizeMobileAction,
        private readonly LockLatestOtpAction             $lockLatestOtpAction,
        private readonly ValidateOtpAction               $validateOtpAction,
        private readonly ApplyOtpValidationFailureAction $applyOtpValidationFailureAction,
        private readonly MarkOtpAsUsedAction             $markOtpAsUsedAction,
    )
    {
    }

    /**
     * @template TResult
     *
     * @param Closure(Otp): TResult $callback
     *
     * @return TResult
     */
    public function execute(
        string      $mobile,
        string|int  $code,
        OtpTypeEnum $otpType,
        Closure     $callback,
    ): mixed
    {
        $mobile = $this->normalizeMobileAction->execute(
            $mobile
        );

        $code = normalize_number(
            trim((string)$code)
        );

        $this->validateCodeFormat($code);

        $transactionResult = DB::transaction(function () use (
            $mobile,
            $code,
            $otpType,
            $callback,
        ): array {
            $otp = $this->lockLatestOtpAction->execute(
                mobile: $mobile,
                otpType: $otpType,
            );

            $validation = $this->validateOtpAction->execute(
                otp: $otp,
                code: $code,
            );

            if (!$validation->valid) {
                $this
                    ->applyOtpValidationFailureAction
                    ->execute(
                        otp: $otp,
                        result: $validation,
                    );

                return [
                    'successful' => false,
                    'message' => $validation->message,
                    'value' => null,
                ];
            }

            $this->markOtpAsUsedAction->execute($otp);

            return [
                'successful' => true,
                'message' => '',
                'value' => $callback($otp),
            ];
        });

        /*
         * Exception باید بعد از Commit پرتاب شود.
         *
         * در غیر این صورت افزایش attempts یا منقضی کردن OTP
         * به دلیل Rollback از بین می‌رود.
         */
        if (!$transactionResult['successful']) {
            throw new InvalidOtpException(
                $transactionResult['message']
            );
        }

        return $transactionResult['value'];
    }

    private function validateCodeFormat(string $code): void
    {
        $length = min(
            8,
            max(
                4,
                (int)config(
                    'auth-flow.otp.length',
                    6
                )
            )
        );

        if (!preg_match('/^\d{' . $length . '}$/', $code)) {
            throw new InvalidOtpException(
                'کد تایید وارد شده معتبر نیست.'
            );
        }
    }
}
