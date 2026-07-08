<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\RedirectLoggedInUserAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\Identity\Actions\Auth\SendOtpAction;
use App\Application\System\RateLimiterAction;
use App\Domain\Identity\Enums\AuthFlowStepEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use App\Rules\MobileRule;
use App\Rules\ValidPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Component;
use Random\RandomException;


class AuthFlow extends Component
{
    public AuthFlowStepEnum $step;

    public string $username = '';
    public string $mobile = '';
    public string $email = '';
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public ?string $otp_code = null;

    public ?string $otpType = null;

    public function mount(): void
    {
        $this->step = AuthFlowStepEnum::LOGIN;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.front.identity.user.auth.auth-flow');
    }

    public function updatedUsername(): void
    {
        $this->resetErrorBag('username');
    }

    public function goToRegister(): void
    {
        $this->resetValidation();
        $this->reset([
            'username',
            'mobile',
            'email',
            'first_name',
            'last_name',
            'password',
            'password_confirmation',
        ]);

        $this->step = AuthFlowStepEnum::REGISTER;
    }

    public function goToLogin(): void
    {
        $this->resetValidation();
        $this->reset([
            'username',
            'mobile',
            'email',
            'first_name',
            'last_name',
            'password',
            'password_confirmation',
        ]);

        $this->step = AuthFlowStepEnum::LOGIN;
    }

    /**
     * @return void
     */
    public function goToOtpLogin(): void
    {
        $this->resetValidation();
        $this->reset(['otp_code']);

        $this->step = AuthFlowStepEnum::OTP_LOGIN;
    }

    /**
     * @return void
     */
    public function goToEnterUsername(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->step = AuthFlowStepEnum::ENTER_USERNAME;
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function login(): RedirectResponse|Redirector|null
    {
        if ($this->rateLimit('request.login'))
            return null;

        $this->username = normalize_number($this->username);

        $this->validate([
            'username' => ['required', 'regex:/^(09\d{9}|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/'],
            'password' => ['required'],
        ], [
            'username.required' => 'شماره موبایل یا ایمیل را وارد کنید',
            'username.regex' => 'شماره موبایل یا ایمیل اشتباه می باشد',
            'password.required' => 'رمز عبور را وارد کنید',
        ]);

        $loginField = filter_var($this->username, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'mobile';

        if (Auth::attempt([
            $loginField => $this->username,
            'password' => $this->password
        ])) {
            request()->session()->regenerate();
            return (new RedirectLoggedInUserAction())->execute();
        }

        $this->addError('password', 'اطلاعات وارد شده صحیح نمی‌باشد');
        return null;
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function verifyOtp(): RedirectResponse|Redirector|null
    {
        $this->username = normalize_number($this->username);

        $this->validate([
            'otp_code' => 'required',
        ], [
            'otp_code.required' => 'کد تایید را وارد کنید',
        ]);

        if (!$this->validateOtp($this->username, $this->otp_code, OtpTypeEnum::LOGIN)) {
            return null;
        }

        $user = User::where('mobile', $this->username)->first();

        if (!$user) {
            $this->addError('invalid_information', 'اطلاعات وارد شده صحیح نمی باشد');
            return null;
        }

        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        request()->session()->regenerate();

        return app(RedirectLoggedInUserAction::class)->execute();
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function register(): RedirectResponse|Redirector|null
    {
        if ($this->rateLimit('request.register'))
            return null;

        $this->validate([
            'mobile' => 'required|regex:/^09\d{9}$/',
            'email' => 'nullable|email',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => [
                'required',
                'string',
                new ValidPassword(),
                'confirmed',
            ],
            'password_confirmation' => 'required',
        ], [
            'mobile.required' => 'شماره موبایل خود را وارد کنید',
            'mobile.regex' => 'شماره موبایل خود را به طور صحیح وارد کنید',
            'email.email' => 'ایمیل خود را به طور صحیح وارد کنید',
            'first_name.required' => 'نام خود را وارد کنید',
            'last_name.required' => 'نام خانوادگی خود را وارد کنید',
            'password.required' => 'رمز عبور را وارد کنید',
            'password.min' => 'حداقل طول رمز عبور 8 حرف می باشد',
            'password.string' => 'فرمت رمز عبور اشتباه می باشد',
            'password.confirmed' => 'رمزعبور و تکرار رمزعبور مطابقت ندارد',
            'password_confirmation.required' => 'تکرار رمز عبور را وارد کنید',
        ]);

        $user = app(RegisterUserAction::class)->execute(
            firstName: $this->first_name,
            lastName: $this->last_name,
            displayName: $this->first_name . ' ' . $this->last_name,
            mobile: $this->mobile,
            email: $this->email,
            password: $this->password
        );

        Auth::login($user);
        request()->session()->regenerate();

        return app(RedirectLoggedInUserAction::class)->execute();
    }

    /**
     * @throws RandomException
     */
    public function sendOtp(): void
    {
        $this->validate([
            'username' => ['required', new MobileRule()],
        ], [
            'username.required' => 'شماره موبایل را وارد کنید',
            'username.regex' => 'شماره موبایل معتبر نیست',
        ]);

        if (!User::where('mobile', $this->username)->exists()) {
            $this->addError('not_found_mobile', 'کاربری با این شماره موبایل یافت نشد!');
            return;
        }

        $result = app(SendOtpAction::class)->execute($this->username, OtpTypeEnum::LOGIN);

        $this->otpType = OtpTypeEnum::LOGIN->value;
        $this->step = AuthFlowStepEnum::VERIFY_OTP_LOGIN;

        $message = 'کد تایید با موفقیت ارسال شد';

        if ($result?->alreadySent) {
            $message = $result->message;
        }

        session()->flash('success', $message);
    }

    /**
     * @return void
     * @throws RandomException
     */
    public function resendOtp(): void
    {
        $this->resetErrorBag();

        if (!$this->username || !$this->otpType) {
            $this->addError('otp_code', 'شماره موبایل معتبر نیست');
            return;
        }

        $type = OtpTypeEnum::from($this->otpType);
        $result = app(SendOtpAction::class)->execute($this->username, $type);

        if (!$result->sent) {
            $this->addError('otp_code', $result->message);
            return;
        }

        session()->flash('success', $result->message);
    }

    /**
     * @param string $username
     * @param string|int $code
     * @param OtpTypeEnum $type
     * @return bool
     */
    private function validateOtp(string $username, string|int $code, OtpTypeEnum $type): bool
    {
        $this->resetErrorBag();

        $otp = Otp::where('key', $username)
            ->where('type', $type)
            ->latest()
            ->first();

        if (!$otp || $otp->code != $code || $otp->expired_at < now() || $otp->is_used) {
            $this->addError('otp_code', 'کد تایید صحیح نمی باشد');
            return false;
        }

        $otp->update([
            'expired_at' => now(),
            'is_used' => true,
        ]);

        return true;
    }

    /**
     * @param string $action
     * @return bool
     */
    private function rateLimit(string $action): bool
    {
        $this->resetErrorBag();

        $seconds = ((new RateLimiterAction())->execute($action));

        if ($seconds) {
            $this->addError(
                'password',
                "تعداد تلاش‌ بیش از حد مجاز است. لطفاً $seconds ثانیه دیگر تلاش کنید."
            );
            return true;
        }

        return false;
    }

    /**
     * @throws RandomException
     */
    public function sendResetPasswordOtp(): void
    {
        $this->username = normalize_number($this->username);

        $this->validate([
            'username' => ['required', new MobileRule()],
        ], [
            'username.required' => 'شماره موبایل را وارد کنید',
            'username.regex' => 'شماره موبایل معتبر نیست',
        ]);

        if (!User::where('mobile', $this->username)->exists()) {
            $this->addError('not_found_mobile', 'کاربری با این شماره موبایل یافت نشد!');
            return;
        }

        $result = app(SendOtpAction::class)->execute($this->username, OtpTypeEnum::RESET_PASSWORD);

        $this->otpType = OtpTypeEnum::RESET_PASSWORD->value;
        $this->step = AuthFlowStepEnum::FORGOT_PASSWORD;

        if ($result?->alreadySent) {
            $message = $result->message;
        } else {
            $message = 'کد تایید ارسال شد';
        }

        session()->flash('success', $message);
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function forgotPassword(): RedirectResponse|Redirector|null
    {
        $this->username = normalize_number($this->username);
        $this->otp_code = normalize_number($this->otp_code);

        if ($this->rateLimit('request.forgot-password'))
            return null;

        $this->validate([
            'username' => ['required', new MobileRule()],
            'otp_code' => ['required'],
            'password' => [
                'required',
                'string',
                new ValidPassword(),
                'confirmed',
            ],
            'password_confirmation' => ['required'],
        ], [
            'username.required' => 'شماره موبایل را وارد کنید',
            'username.regex' => 'شماره موبایل اشتباه می باشد',
            'otp_code.required' => 'کد تایید را وارد کنید',
            'password.required' => 'رمز عبور را وارد کنید',
            'password_confirmation.required' => 'تکرار رمز عبور را وارد کنید',
            'password.min' => 'حداقل طول رمز عبور :min حرف می باشد',
            'password.string' => 'فرمت رمز عبور اشتباه می باشد',
            'password.confirmed' => 'رمزعبور و تکرار رمزعبور مطابقت ندارد',
        ]);

        if (!$this->validateOtp($this->username, $this->otp_code, OtpTypeEnum::RESET_PASSWORD))
            return null;

        $user = User::where('mobile', $this->username)->orWhere('email', $this->username)->first();

        if (!$user) {
            $this->addError('invalid_information', 'اطلاعات وارد شده صحیح نمی باشد');
            return null;
        }

        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        request()->session()->regenerate();

        return (new RedirectLoggedInUserAction())->execute();
    }
}
