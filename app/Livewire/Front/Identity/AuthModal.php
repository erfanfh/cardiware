<?php

namespace App\Livewire\Front\Identity;

use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Identity\Actions\Auth\CompleteAuthModalAction;
use App\Application\Identity\Actions\Auth\LoginUserWithEmailAction;
use App\Application\Identity\Actions\Auth\LoginUserWithOtpAction;
use App\Application\Identity\Actions\Auth\LoginUserWithPasswordAction;
use App\Application\Identity\Actions\Auth\RegisterUserWithOtpAction;
use App\Application\Identity\Actions\Auth\RequestLoginOtpAction;
use App\Application\Identity\Actions\Auth\RequestPasswordResetOtpAction;
use App\Application\Identity\Actions\Auth\RequestRegistrationOtpAction;
use App\Application\Identity\Actions\Auth\ResetUserPasswordWithOtpAction;
use App\Application\Identity\Actions\Auth\StartAuthFlowAction;
use App\Application\Identity\DTOs\RegisterUserWithOtpDTO;
use App\Application\Identity\DTOs\ResetUserPasswordWithOtpDTO;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Exceptions\AuthRateLimitExceededException;
use App\Application\Identity\Exceptions\EmailAlreadyRegisteredException;
use App\Application\Identity\Exceptions\InvalidCredentialsException;
use App\Application\Identity\Exceptions\InvalidMobileException;
use App\Application\Identity\Exceptions\InvalidOtpException;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Enums\AuthFlowStepEnum;
use App\Domain\Identity\Models\User;
use App\Rules\MobileRule;
use App\Rules\ValidPassword;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Random\RandomException;
use Throwable;

final class AuthModal extends Component
{
    #[Locked]
    public string $name = 'auth-modal';

    #[Locked]
    public bool $claimCurrentCart = false;

    #[Locked]
    public bool $openCheckoutAfterAuth = false;

    #[Locked]
    public string $redirectUrl = '';

    #[Locked]
    public AuthFlowStepEnum $step;

    public string $mobile = '';

    #[Locked]
    public string $resolvedMobile = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $otpCode = '';

    public string $firstName = '';

    public string $lastName = '';

    public ?string $email = null;

    #[Locked]
    public int $otpResendAvailableAt = 0;

    #[Locked]
    public int $otpExpiresAt = 0;

    public function mount(
        string  $name = 'auth-modal',
        bool    $claimCurrentCart = false,
        bool    $openCheckoutAfterAuth = false,
        ?string $redirectUrl = null,
    ): void
    {
        $this->name = $name;
        $this->claimCurrentCart = $claimCurrentCart;
        $this->openCheckoutAfterAuth = $openCheckoutAfterAuth;
        $this->redirectUrl = $redirectUrl ?: url()->current();

        $this->step = AuthFlowStepEnum::ENTER_MOBILE;
    }

    public function render(): View
    {
        return view(
            'livewire.front.identity.auth-modal'
        );
    }

    public function updated(string $property): void
    {
        $this->resetErrorBag($property);
    }

    /**
     * تشخیص اینکه شماره مربوط به ورود است یا ثبت‌نام.
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
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'mobile',
            );

            return;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'mobile',
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
        GetCurrentActiveCartAction  $getCurrentActiveCartAction,
        CompleteAuthModalAction     $completeAuthModalAction,
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

        /*
         * سبد مهمان باید قبل از Auth::login خوانده شود.
         */
        $guestCart = $this->captureGuestCart(
            $getCurrentActiveCartAction
        );

        try {
            $user = $loginUserWithPasswordAction->execute(
                mobile: $this->resolvedMobile,
                password: $this->password,
            );
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'password',
            );

            return null;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'password',
            );

            return null;
        }

        return $this->finishAuthentication(
            user: $user,
            guestCart: $guestCart,
            completeAuthModalAction: $completeAuthModalAction,
        );
    }

    public function goToEmailLogin(): void
    {
        if ($this->step !== AuthFlowStepEnum::ENTER_MOBILE) {
            return;
        }

        $this->goToEnterMobile();
        $this->step = AuthFlowStepEnum::LOGIN_EMAIL;
    }

    public function loginWithEmail(
        LoginUserWithEmailAction $loginUserWithEmailAction,
        GetCurrentActiveCartAction $getCurrentActiveCartAction,
        CompleteAuthModalAction $completeAuthModalAction,
    ): RedirectResponse|Redirector|null
    {
        if ($this->step !== AuthFlowStepEnum::LOGIN_EMAIL) {
            return null;
        }

        $this->resetValidation();
        $this->email = Str::lower(trim($this->email ?? ''));

        $this->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.*' => 'اطلاعات وارد شده صحیح نیست.',
            'password.*' => 'اطلاعات وارد شده صحیح نیست.',
        ]);

        $guestCart = $this->captureGuestCart($getCurrentActiveCartAction);

        try {
            $user = $loginUserWithEmailAction->execute($this->email, $this->password);
        } catch (InvalidCredentialsException|AuthRateLimitExceededException $exception) {
            $this->addError('emailLogin', 'اطلاعات وارد شده صحیح نیست.');

            return null;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(exception: $exception, field: 'emailLogin');

            return null;
        }

        return $this->finishAuthentication(
            user: $user,
            guestCart: $guestCart,
            completeAuthModalAction: $completeAuthModalAction,
        );
    }

    /**
     * ارسال OTP ورود.
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
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'password',
            );

            return;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'password',
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
     * ارسال OTP فراموشی رمز.
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
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'password',
            );

            return;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'password',
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
     * ورود با OTP.
     */
    public function verifyLoginOtp(
        LoginUserWithOtpAction     $loginUserWithOtpAction,
        GetCurrentActiveCartAction $getCurrentActiveCartAction,
        CompleteAuthModalAction    $completeAuthModalAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeOtpCode();
        $this->validateOtpCode();

        $guestCart = $this->captureGuestCart(
            $getCurrentActiveCartAction
        );

        try {
            $user = $loginUserWithOtpAction->execute(
                mobile: $this->resolvedMobile,
                otpCode: $this->otpCode,
            );
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'otpCode',
            );

            return null;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'otpCode',
            );

            return null;
        }

        return $this->finishAuthentication(
            user: $user,
            guestCart: $guestCart,
            completeAuthModalAction: $completeAuthModalAction,
        );
    }

    /**
     * ثبت‌نام همراه با OTP.
     */
    public function register(
        RegisterUserWithOtpAction  $registerUserWithOtpAction,
        GetCurrentActiveCartAction $getCurrentActiveCartAction,
        CompleteAuthModalAction    $completeAuthModalAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeRegistrationInputs();
        $this->validateRegistration();

        $guestCart = $this->captureGuestCart(
            $getCurrentActiveCartAction
        );

        try {
            $user = $registerUserWithOtpAction->execute(
                new RegisterUserWithOtpDTO(
                    mobile: $this->resolvedMobile,
                    otpCode: $this->otpCode,
                    firstName: $this->firstName,
                    lastName: $this->lastName,
                    email: $this->email,
                    password: $this->password,
                )
            );
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'registration',
            );

            return null;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'registration',
            );

            return null;
        }

        return $this->finishAuthentication(
            user: $user,
            guestCart: $guestCart,
            completeAuthModalAction: $completeAuthModalAction,
        );
    }

    /**
     * تغییر رمز با OTP و ورود خودکار.
     */
    public function resetPassword(
        ResetUserPasswordWithOtpAction $resetUserPasswordWithOtpAction,
        GetCurrentActiveCartAction     $getCurrentActiveCartAction,
        CompleteAuthModalAction        $completeAuthModalAction,
    ): RedirectResponse|Redirector|null
    {
        $this->normalizeOtpCode();
        $this->validatePasswordReset();

        $guestCart = $this->captureGuestCart(
            $getCurrentActiveCartAction
        );

        try {
            $user = $resetUserPasswordWithOtpAction->execute(
                new ResetUserPasswordWithOtpDTO(
                    mobile: $this->resolvedMobile,
                    otpCode: $this->otpCode,
                    password: $this->password,
                )
            );
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'resetPassword',
            );

            return null;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'resetPassword',
            );

            return null;
        }

        return $this->finishAuthentication(
            user: $user,
            guestCart: $guestCart,
            completeAuthModalAction: $completeAuthModalAction,
        );
    }

    /**
     * ارسال مجدد OTP براساس مرحله فعلی.
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
        } catch (DomainException $exception) {
            $this->addDomainError(
                exception: $exception,
                defaultField: 'otpCode',
            );

            return;
        } catch (Throwable $exception) {
            $this->handleUnexpectedError(
                exception: $exception,
                field: 'otpCode',
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

    public function goToEnterMobile(): void
    {
        $mobile = $this->resolvedMobile ?: $this->mobile;

        $this->resetValidation();

        $this->resetFormFields();

        $this->mobile = $mobile;
        $this->resolvedMobile = '';

        $this->clearOtpTiming();

        $this->step = AuthFlowStepEnum::ENTER_MOBILE;
    }

    /**
     * هنگام بستن Modal صدا زده می‌شود.
     */
    public function resetAuthFlow(): void
    {
        $this->resetValidation();

        $this->mobile = '';
        $this->resolvedMobile = '';
        $this->step = AuthFlowStepEnum::ENTER_MOBILE;

        $this->resetFormFields();
        $this->clearOtpTiming();
    }

    private function captureGuestCart(
        GetCurrentActiveCartAction $getCurrentActiveCartAction,
    ): ?Cart
    {
        if (!$this->claimCurrentCart) {
            return null;
        }

        return $getCurrentActiveCartAction->execute();
    }

    private function finishAuthentication(
        User                    $user,
        ?Cart                   $guestCart,
        CompleteAuthModalAction $completeAuthModalAction,
    ): RedirectResponse|Redirector
    {
        $result = $completeAuthModalAction->execute(
            user: $user,
            guestCart: $guestCart,
            claimCurrentCart: $this->claimCurrentCart,
            openCheckoutAfterAuth: $this->openCheckoutAfterAuth,
        );

        if ($result->warningMessage) {
            session()->flash(
                'warning',
                $result->warningMessage
            );
        }

        $this->resetSensitiveFields();

        return redirect()->to(
            $this->redirectUrl
        );
    }

    private function normalizeRegistrationInputs(): void
    {
        $this->firstName = trim($this->firstName);
        $this->lastName = trim($this->lastName);
        $this->normalizeOtpCode();

        $this->email = filled($this->email)
            ? Str::lower(trim($this->email))
            : null;
    }

    private function validateRegistration(): void
    {
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
            'firstName.max' => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'lastName.required' => 'نام خانوادگی خود را وارد کنید.',
            'lastName.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلا در سیستم ثبت شده است.',

            'password.required' => 'رمز عبور را وارد کنید.',

            'passwordConfirmation.required' => 'تکرار رمز عبور را وارد کنید.',
            'passwordConfirmation.same' => 'رمز عبور و تکرار آن مطابقت ندارند.',

            'otpCode.required' => 'کد تایید را وارد کنید.',
            'otpCode.digits' => 'کد تایید وارد شده معتبر نیست.',
        ]);
    }

    private function validatePasswordReset(): void
    {
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
            'auth-modal-otp-timing-updated',
            modalName: $this->name,
            resendAvailableAt: $this->otpResendAvailableAt,
            expiresAt: $this->otpExpiresAt,
        );
    }

    private function normalizeOtpCode(): void
    {
        $this->otpCode = normalize_number(
            trim($this->otpCode)
        );
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

    private function addDomainError(
        DomainException $exception,
        string          $defaultField,
    ): void
    {
        $field = match (true) {
            $exception instanceof EmailAlreadyRegisteredException => 'email',
            $exception instanceof InvalidMobileException => 'mobile',
            $exception instanceof InvalidCredentialsException => 'password',
            $exception instanceof InvalidOtpException => 'otpCode',
            default => $defaultField,
        };

        $this->addError(
            $field,
            $exception->getMessage()
        );
    }

    private function handleUnexpectedError(
        Throwable $exception,
        string    $field,
    ): void
    {
        report($exception);

        $this->addError(
            $field,
            'خطایی رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.'
        );
    }

    private function clearOtpTiming(): void
    {
        $this->otpResendAvailableAt = 0;
        $this->otpExpiresAt = 0;
    }

    private function resetSensitiveFields(): void
    {
        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
        ]);
    }

    private function resetFormFields(): void
    {
        $this->reset([
            'password',
            'passwordConfirmation',
            'otpCode',
            'firstName',
            'lastName',
            'email',
        ]);
    }
}
