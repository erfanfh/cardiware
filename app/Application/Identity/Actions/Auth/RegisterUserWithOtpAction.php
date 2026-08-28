<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\Otp\ExecuteWithVerifiedOtpAction;
use App\Application\Identity\Actions\User\MarkUserMobileAsVerifiedAction;
use App\Application\Identity\DTOs\RegisterUserDTO;
use App\Application\Identity\DTOs\RegisterUserWithOtpDTO;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterUserWithOtpAction
{
    public function __construct(
        private readonly NormalizeMobileAction                      $normalizeMobileAction,
        private readonly ConsumeAuthRateLimitAction                 $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction                   $clearAuthRateLimitAction,
        private readonly ExecuteWithVerifiedOtpAction               $executeWithVerifiedOtpAction,
        private readonly EnsureMobileAvailableForRegistrationAction $ensureMobileAvailableForRegistrationAction,
        private readonly RegisterUserAction                         $registerUserAction,
        private readonly MarkUserMobileAsVerifiedAction             $markUserMobileAsVerifiedAction,
        private readonly AuthenticateUserAction                     $authenticateUserAction,
    )
    {
    }

    public function execute(
        RegisterUserWithOtpDTO $data,
    ): User
    {
        $mobile = $this->normalizeMobileAction->execute(
            $data->mobile
        );

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::REGISTER,
            identifier: $mobile,
        );

        /** @var User $user */
        $user = $this->executeWithVerifiedOtpAction->execute(
            mobile: $mobile,
            code: $data->otpCode,
            otpType: OtpTypeEnum::REGISTER,
            callback: function (Otp $otp) use ($data): User {
                /*
                 * شماره داخل تراکنش دوباره بررسی می‌شود تا
                 * Race Condition میان ارسال OTP و ثبت‌نام کنترل شود.
                 */
                $mobile = $this
                    ->ensureMobileAvailableForRegistrationAction
                    ->execute($otp->key);

                $user = $this->registerUserAction->execute(
                    new RegisterUserDTO(
                        firstName: $data->firstName,
                        lastName: $data->lastName,
                        mobile: $mobile,
                        password: $data->password,
                        email: $data->email,
                    )
                );

                return $this
                    ->markUserMobileAsVerifiedAction
                    ->execute($user);
            },
        );

        $registeredAt = $user->created_at?->toImmutable()
            ?? now(config('app.timezone'))->toImmutable();

        DB::afterCommit(
            fn() => event(
                new UserRegisteredEvent(
                    userId: (int)$user->getKey(),
                    occurredAt: $registeredAt,
                )
            )
        );

        /*
         * ورود کاربر باید بعد از Commit ثبت‌نام باشد.
         */
        $this->authenticateUserAction->execute($user);

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::REGISTER,
            identifier: $mobile,
        );

        return $user;
    }
}
