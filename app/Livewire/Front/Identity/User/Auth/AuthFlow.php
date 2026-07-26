<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\LoginUserWithOtpAction;
use App\Application\Identity\Actions\Auth\LoginUserWithPasswordAction;
use App\Application\Identity\Actions\Auth\RedirectLoggedInUserAction;
use App\Application\Identity\Actions\Auth\RegisterUserWithOtpAction;
use App\Application\Identity\Actions\Auth\RequestLoginOtpAction;
use App\Application\Identity\Actions\Auth\RequestPasswordResetOtpAction;
use App\Application\Identity\Actions\Auth\RequestRegistrationOtpAction;
use App\Application\Identity\Actions\Auth\ResetUserPasswordWithOtpAction;
use App\Application\Identity\Actions\Auth\StartAuthFlowAction;
use App\Application\Identity\DTOs\RegisterUserWithOtpDTO;
use App\Application\Identity\DTOs\ResetUserPasswordWithOtpDTO;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Application\Identity\Exceptions\AuthRateLimitExceededException;
use App\Application\Identity\Exceptions\EmailAlreadyRegisteredException;
use App\Application\Identity\Exceptions\InvalidCredentialsException;
use App\Application\Identity\Exceptions\InvalidMobileException;
use App\Application\Identity\Exceptions\InvalidOtpException;
use App\Application\Identity\Exceptions\MobileAlreadyRegisteredException;
use App\Application\Identity\Exceptions\OtpDeliveryFailedException;
use App\Application\Identity\Exceptions\OtpRequestInProgressException;
use App\Application\Identity\Exceptions\RegistrationRoleNotFoundException;
use App\Application\Identity\Exceptions\UserNotFoundException;
use App\Domain\Identity\Enums\AuthFlowStepEnum;
use App\Domain\Identity\Models\User;
use App\Rules\MobileRule;
use App\Rules\ValidPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Random\RandomException;

final class AuthFlow extends Component
{
    #[Locked]
    public AuthFlowStepEnum $step;

    /**
     * فقط در صفحه اول توسط کاربر ویرایش می‌شود.
     */
    public string $mobile = '';

    /**
     * شماره تاییدشده و نرمال‌شده در Flow.
     *
     * تمام Actionهای بعدی باید از این property استفاده کنند،
     * نه مقدار قابل ویرایش mobile.
     */
    #[Locked]
    public string $resolvedMobile = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $otpCode = '';

    public string $firstName = '';

    public string $lastName = '';

    public ?string $email = null;

    /**
     * Unix timestamp مربوط به زمان فعال شدن ارسال مجدد.
     */
    #[Locked]
    public int $otpResendAvailableAt = 0;

    /**
     * Unix timestamp مربوط به انقضای OTP فعلی.
     */
    #[Locked]
    public int $otpExpiresAt = 0;

    public function mount(): void
    {
        $this->step = AuthFlowStepEnum::ENTER_MOBILE;
    }

    public function render(): View
    {
        return view(
            'livewire.front.identity.user.auth.auth-flow'
        );
    }

    /**
     * حذف خطای همان فیلد هنگام تغییر مقدار.
     */
    public function updated(string $property): void
    {
        $this->resetErrorBag($property);
    }

    /**
     * صفحه اول:
     * تشخیص ورود یا ثبت‌نام براساس شماره موبایل.
     *
     * @throws RandomException
     */
    public function startAuthFlow(
        StartAuthFlowAction $startAuthFlowAction,
    ): void
    {
        $this->resetValidation();

        $this->mobile = normalize_number(
            trim($this->mobile)
        );

        $this->validate([
            'mobile' => [
                'required',
                new MobileRule(),
            ],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
        ]);

        try {
            $result = $startAuthFlowAction->execute(
                $this->mobile
            );
        } catch (
        InvalidMobileException|
        AccountUnavailableException|
        AuthRateLimitExceededException|
        OtpDeliveryFailedException|
        OtpRequestInProgressException|
        MobileAlreadyRegisteredException $exception
        ) {
            $this->addError(
                'mobile',
                $exception->getMessage()
            );

            return;
        }

        $this->mobile = $result->mobile;
        $this->resolvedMobile = $result->mobile;
        $this->step = $result->nextStep;

        $this->resetSensitiveFields();

        if ($result->otpResult) {
            $this->applyOtpResult(
                result: $result->otpResult,
                nextStep: AuthFlowStepEnum::REGISTER,
            );
        }
    }

    /**
     * ورود با رمز عبور.
     */
    public function loginWithPassword(
        LoginUserWithPasswordAction $loginUserWithPasswordAction,
        RedirectLoggedInUserAction  $redirectLoggedInUserAction,
    ): RedirectResponse|Redirector|null
    {
        $this->resetErrorBag('password');

        $this->validate([
            'password' => [
                'required',
                'string',
            ],
        ], [
            'password.required' => 'رمز عبور را وارد کنید.',
        ]);

        try {
            $loginUserWithPasswordAction->execute(
                mobile: $this->resolvedMobile,
                password: $this->password,
            );
        } catch (
        InvalidMobileException|
        InvalidCredentialsException|
        AccountUnavailableException|
        UserNotFoundException|
        AuthRateLimitExceededException $exception
        ) {
            $this->addError(
                'password',
                $exception->getMessage()
            );

            return null;
        }

        $this->resetSensitiveFields();

        return $redirectLoggedInUserAction->execute();
    }

    /**
     * ارسال OTP ورود و انتقال به صفحه تایید.
     *
     * @throws RandomException
     */
    public function requestLoginOtp(
        RequestLoginOtpAction $requestLoginOtpAction,
    ): void
    {
        $this->resetValidation();

        try {
            $result = $requestLoginOtpAction->execute(
                $this->resolvedMobile
            );
        } catch (
        InvalidMobileException|
        AccountUnavailableException|
        UserNotFoundException|
        AuthRateLimitExceededException|
        OtpRequestInProgressException $exception
        ) {
            $this->addError(
                'password',
                $exception->getMessage()
            );

            return;
        }

        if (!$this->otpRequestSucceeded($result)) {
            return;
        }

        $this->password = '';
        $this->otpCode = '';

        $this->applyOtpResult(
            result: $result,
            nextStep: AuthFlowStepEnum::VERIFY_LOGIN_OTP,
        );
    }

    /**
     * ارسال OTP فراموشی رمز و انتقال به صفحه تغییر رمز.
     *
     * @throws RandomException
     */
    public function requestPasswordResetOtp(
        RequestPasswordResetOtpAction $requestPasswordResetOtpAction,
    ): void
    {
        $this->resetValidation();

        try {
            $result = $requestPasswordResetOtpAction->execute(
                $this->resolvedMobile
            );
        } catch (
        InvalidMobileException|
        AccountUnavailableException|
        UserNotFoundException|
        AuthRateLimitExceededException|
        OtpRequestInProgressException $exception
        ) {
            $this->addError(
                'password',
                $exception->getMessage()
            );

            return;
        }

        if (!$this->otpRequestSucceeded($result)) {
            return;
        }

        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
        ]);

        $this->applyOtpResult(
            result: $result,
            nextStep: AuthFlowStepEnum::RESET_PASSWORD,
        );
    }

    /**
     * ورود نهایی با OTP.
     */
    public function verifyLoginOtp(
        LoginUserWithOtpAction     $loginUserWithOtpAction,
        RedirectLoggedInUserAction $redirectLoggedInUserAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeOtpCode();

        $this->validateOtpCode();

        try {
            $loginUserWithOtpAction->execute(
                mobile: $this->resolvedMobile,
                otpCode: $this->otpCode,
            );
        } catch (
        InvalidMobileException|
        InvalidOtpException|
        AccountUnavailableException|
        UserNotFoundException|
        AuthRateLimitExceededException $exception
        ) {
            $this->addError(
                'otpCode',
                $exception->getMessage()
            );

            return null;
        }

        $this->resetSensitiveFields();

        return $redirectLoggedInUserAction->execute();
    }

    /**
     * ثبت‌نام نهایی با اطلاعات کاربر و OTP.
     */
    public function register(
        RegisterUserWithOtpAction  $registerUserWithOtpAction,
        RedirectLoggedInUserAction $redirectLoggedInUserAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeOtpCode();

        $this->firstName = trim($this->firstName);
        $this->lastName = trim($this->lastName);

        $this->email = filled($this->email)
            ? Str::lower(trim($this->email))
            : null;

        $this->validate([
            'firstName' => [
                'required',
                'string',
                'max:100',
            ],
            'lastName' => [
                'required',
                'string',
                'max:100',
            ],
            'email' => [
                'nullable',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'password' => [
                'required',
                'string',
                new ValidPassword(),
            ],
            'passwordConfirmation' => [
                'required',
                'string',
                'same:password',
            ],
            'otpCode' => [
                'required',
                'digits:' . $this->otpLength(),
            ],
        ], [
            'firstName.required' => 'نام خود را وارد کنید.',
            'lastName.required' => 'نام خانوادگی خود را وارد کنید.',

            'email.email' => 'ایمیل وارد شده صحیح نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلا در سیستم ثبت شده است.',

            'password.required' => 'رمز عبور را وارد کنید.',

            'passwordConfirmation.required' => 'تکرار رمز عبور را وارد کنید.',
            'passwordConfirmation.same' => 'رمز عبور و تکرار آن مطابقت ندارند.',

            'otpCode.required' => 'کد تایید را وارد کنید.',
            'otpCode.digits' => 'کد تایید وارد شده معتبر نیست.',
        ]);

        try {
            $registerUserWithOtpAction->execute(
                new RegisterUserWithOtpDTO(
                    mobile: $this->resolvedMobile,
                    otpCode: $this->otpCode,
                    firstName: $this->firstName,
                    lastName: $this->lastName,
                    email: filled($this->email)
                        ? $this->email
                        : null,
                    password: $this->password,
                )
            );
        } catch (
        InvalidMobileException|
        InvalidOtpException|
        MobileAlreadyRegisteredException|
        EmailAlreadyRegisteredException|
        AccountUnavailableException|
        RegistrationRoleNotFoundException|
        AuthRateLimitExceededException $exception
        ) {
            $field = $exception instanceof EmailAlreadyRegisteredException
                ? 'email'
                : 'registration';

            $this->addError(
                $field,
                $exception->getMessage()
            );

            return null;
        }

        $this->resetSensitiveFields();

        return $redirectLoggedInUserAction->execute();
    }

    /**
     * تغییر رمز با OTP.
     */
    public function resetPassword(
        ResetUserPasswordWithOtpAction $resetUserPasswordWithOtpAction,
        RedirectLoggedInUserAction     $redirectLoggedInUserAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeOtpCode();

        $this->validate([
            'password' => [
                'required',
                'string',
                new ValidPassword(),
            ],
            'passwordConfirmation' => [
                'required',
                'string',
                'same:password',
            ],
            'otpCode' => [
                'required',
                'digits:' . $this->otpLength(),
            ],
        ], [
            'password.required' => 'رمز عبور جدید را وارد کنید.',

            'passwordConfirmation.required' => 'تکرار رمز عبور را وارد کنید.',
            'passwordConfirmation.same' => 'رمز عبور و تکرار آن مطابقت ندارند.',

            'otpCode.required' => 'کد تایید را وارد کنید.',
            'otpCode.digits' => 'کد تایید وارد شده معتبر نیست.',
        ]);

        try {
            $resetUserPasswordWithOtpAction->execute(
                new ResetUserPasswordWithOtpDTO(
                    mobile: $this->resolvedMobile,
                    otpCode: $this->otpCode,
                    password: $this->password,
                )
            );
        } catch (
        InvalidMobileException|
        InvalidOtpException|
        AccountUnavailableException|
        UserNotFoundException|
        AuthRateLimitExceededException $exception
        ) {
            $this->addError(
                'resetPassword',
                $exception->getMessage()
            );

            return null;
        }

        $this->resetSensitiveFields();

        return $redirectLoggedInUserAction->execute();
    }

    /**
     * ارسال مجدد OTP براساس مرحله فعلی.
     *
     * نوع OTP از سمت سرور و براساس step انتخاب می‌شود؛
     * بنابراین نیازی به property قابل تغییر otpType نداریم.
     *
     * @throws RandomException
     */
    public function resendOtp(
        RequestLoginOtpAction         $requestLoginOtpAction,
        RequestRegistrationOtpAction  $requestRegistrationOtpAction,
        RequestPasswordResetOtpAction $requestPasswordResetOtpAction,
    ): void
    {
        $this->resetErrorBag('otpCode');

        try {
            $result = match ($this->step) {
                AuthFlowStepEnum::VERIFY_LOGIN_OTP =>
                $requestLoginOtpAction->execute(
                    $this->resolvedMobile
                ),

                AuthFlowStepEnum::REGISTER =>
                $requestRegistrationOtpAction->execute(
                    $this->resolvedMobile
                ),

                AuthFlowStepEnum::RESET_PASSWORD =>
                $requestPasswordResetOtpAction->execute(
                    $this->resolvedMobile
                ),

                default => null,
            };
        } catch (
        InvalidMobileException|
        AccountUnavailableException|
        UserNotFoundException|
        MobileAlreadyRegisteredException|
        AuthRateLimitExceededException|
        OtpRequestInProgressException $exception
        ) {
            $this->addError(
                'otpCode',
                $exception->getMessage()
            );

            return;
        }

        if (!$result) {
            $this->addError(
                'otpCode',
                'امکان ارسال کد در این مرحله وجود ندارد.'
            );

            return;
        }

        if (!$this->otpRequestSucceeded($result)) {
            return;
        }

        $this->otpCode = '';

        $this->syncOtpTiming($result);

        session()->flash(
            'success',
            $result->message
        );
    }

    /**
     * بازگشت به صفحه ورود با رمز.
     */
    public function goToPasswordLogin(): void
    {
        if (blank($this->resolvedMobile)) {
            $this->goToEnterMobile();

            return;
        }

        $this->resetValidation();

        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
            'firstName',
            'lastName',
            'email',
        ]);

        $this->clearOtpTiming();

        $this->step = AuthFlowStepEnum::LOGIN_PASSWORD;
    }

    /**
     * ویرایش شماره موبایل و شروع مجدد Flow.
     */
    public function goToEnterMobile(): void
    {
        $mobile = $this->resolvedMobile ?: $this->mobile;

        $this->resetValidation();

        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
            'firstName',
            'lastName',
            'email',
        ]);

        $this->mobile = $mobile;
        $this->resolvedMobile = '';

        $this->clearOtpTiming();

        $this->step = AuthFlowStepEnum::ENTER_MOBILE;
    }

    private function otpRequestSucceeded(
        SendOtpResultDTO $result,
    ): bool
    {
        if ($result->sent || $result->alreadySent) {
            return true;
        }

        $this->addError(
            'otpCode',
            $result->message
        );

        return false;
    }

    private function applyOtpResult(
        SendOtpResultDTO $result,
        AuthFlowStepEnum $nextStep,
    ): void
    {
        $this->syncOtpTiming($result);

        $this->step = $nextStep;

        session()->flash(
            'success',
            $result->message
        );
    }

    private function syncOtpTiming(
        SendOtpResultDTO $result,
    ): void
    {
        $this->otpResendAvailableAt = now()
            ->addSeconds(
                max(0, $result->retryAfterSeconds)
            )
            ->timestamp;

        $this->otpExpiresAt = now()
            ->addSeconds(
                max(0, $result->expiresAfterSeconds)
            )
            ->timestamp;

        $this->dispatch(
            'otp-timing-updated',
            resendAvailableAt: $this->otpResendAvailableAt,
            expiresAt: $this->otpExpiresAt,
        );
    }

    private function clearOtpTiming(): void
    {
        $this->otpResendAvailableAt = 0;
        $this->otpExpiresAt = 0;
    }

    private function normalizeOtpCode(): void
    {
        $this->otpCode = normalize_number(
            trim($this->otpCode)
        );
    }

    private function validateOtpCode(): void
    {
        $this->validate([
            'otpCode' => [
                'required',
                'digits:' . $this->otpLength(),
            ],
        ], [
            'otpCode.required' => 'کد تایید را وارد کنید.',
            'otpCode.digits' => 'کد تایید وارد شده معتبر نیست.',
        ]);
    }

    private function otpLength(): int
    {
        return min(
            8,
            max(
                4,
                (int)config(
                    'auth-flow.otp.length',
                    6
                )
            )
        );
    }

    private function resetSensitiveFields(): void
    {
        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
        ]);
    }
}
