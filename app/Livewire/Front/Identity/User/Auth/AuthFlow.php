<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
use App\Application\Identity\Actions\Auth\RedirectLoggedInUserAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\System\RateLimiterAction;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Component;
use Random\RandomException;


class AuthFlow extends Component
{
    public string $step = 'login';
    public string $username = '';
    public string $mobile = '';
    public string $email = '';
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public ?string $otp_code = null;

    public ?string $otpType = null;

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

        $this->step = 'register';
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

        $this->step = 'login';
    }

    /**
     * @return void
     */
    public function goToOtpLogin(): void
    {
        $this->resetValidation();
        $this->reset(['otp_code']);

        $this->step = 'otp-login-username';
    }


    /**
     * @return void
     */
    public function goToEnterUsername(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->step = 'enter-username';
    }

    /**
     * @return void
     * @throws RandomException
     */
    public function goToLoginByOtp(): void
    {
        $this->resetErrorBag();

        if (!User::where('mobile', $this->username)->orWhere('email', $this->username)->exists()) {
            $this->addError('username', 'کاربری با این شماره موبایل یا ایمیل ثبت نشده است.');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->username, OtpTypeEnum::LOGIN);

        if (!$sent) {
            $this->addError('otp_code', 'برای ارسال مجدد کد تایید باید 60 ثانیه صبر کنید');
            return;
        }

        $this->otpType = OtpTypeEnum::LOGIN->value;
        $this->step = 'otp-login';
        session()->flash('success', 'کد تایید با موفقیت ارسال شد.');
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

        $this->addError('password', 'شماره موبایل یا ایمیل یا رمز عبور اشتباه است.');
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

        $user = User::where('mobile', $this->username)->orWhere('email', $this->username)->firstOrFail();
        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        request()->session()->regenerate();

        return (new RedirectLoggedInUserAction())->execute();
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
            'email' => 'required|email',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => 'required',
        ], [
            'mobile.required' => 'شماره موبایل خود را وارد کنید',
            'mobile.regex' => 'شماره موبایل خود را به طور صحیح وارد کنید',
            'email.required' => 'ایمیل خود را وارد کنید',
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

        return (new RedirectLoggedInUserAction())->execute();
    }

    public function sendOtp(): void
    {
        $this->validate([
            'username' => ['required', 'regex:/^(09\d{9}|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/'],
        ], [
            'username.required' => 'شماره موبایل یا ایمیل را وارد کنید',
            'username.regex' => 'شماره موبایل یا ایمیل معتبر نیست',
        ]);

        if (!User::where('mobile', $this->username)->orWhere('email', $this->username)->exists()) {
            $this->addError('username', 'کاربری با این شماره موبایل یا ایمیل ثبت نشده است');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->username, OtpTypeEnum::LOGIN);

        if (!$sent) {
            $this->addError('username', 'برای ارسال مجدد کد باید کمی صبر کنید');
            return;
        }

        $this->otpType = OtpTypeEnum::LOGIN->value;
        $this->step = 'otp-login-verify';

        session()->flash('success', 'کد تایید ارسال شد');
    }


    /**
     * @return void
     * @throws RandomException
     */
    public function resendOtp(): void
    {
        $this->resetErrorBag();

        if (!$this->username || !$this->otpType) {
            $this->addError('otp_code', 'شماره موبایل یا ایمیل معتبر نیست');
            return;
        }

        $type = OtpTypeEnum::from($this->otpType);
        $sent = (new GenerateOtpAction())->execute($this->username, $type);

        if (!$sent) {
            $this->addError('otp_code', 'برای ارسال مجدد کد تایید باید 60 ثانیه صبر کنید');
            return;
        }

        session()->flash('success', 'کد تایید با موفقیت ارسال شد');
    }

    /**
     * @return void
     */
    public function resetToUsername(): void
    {
        $this->resetValidation();
        $this->reset([
            'first_name',
            'last_name',
            'password',
            'password_confirmation',
            'otp_code',
            'otpType',
        ]);
        $this->step = 'enter-username';
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

    public function sendResetPasswordOtp(): void
    {
        $this->username = normalize_number($this->username);

        $this->validate([
            'username' => ['required', 'regex:/^(09\d{9}|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/'],
        ], [
            'username.required' => 'شماره موبایل یا ایمیل را وارد کنید',
            'username.regex' => 'شماره موبایل یا ایمیل معتبر نیست',
        ]);

        if (!User::where('mobile', $this->username)->orWhere('email', $this->username)->exists()) {
            $this->addError('username', 'کاربری با این شماره موبایل یا ایمیل ثبت نشده است');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->username, OtpTypeEnum::RESET_PASSWORD);

        if (!$sent) {
            $this->addError('username', 'برای ارسال مجدد کد باید کمی صبر کنید');
            return;
        }

        $this->otpType = OtpTypeEnum::RESET_PASSWORD->value;
        $this->step = 'forgot-password';

        session()->flash('success', 'کد تایید ارسال شد');
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
            'username' => ['required', 'regex:/^(09\d{9}|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/'],
            'otp_code' => ['required'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => ['required'],
        ], [
            'username.required' => 'شماره موبایل یا ایمیل را وارد کنید',
            'username.regex' => 'شماره موبایل یا ایمیل اشتباه می باشد',
            'otp_code.required' => 'کد تایید را وارد کنید',
            'password.required' => 'رمز عبور را وارد کنید',
            'password_confirmation.required' => 'تکرار رمز عبور را وارد کنید',
            'password.min' => 'حداقل طول رمز عبور :min حرف می باشد',
            'password.string' => 'فرمت رمز عبور اشتباه می باشد',
            'password.confirmed' => 'رمزعبور و تکرار رمزعبور مطابقت ندارد',
        ]);

        if (!$this->validateOtp($this->username, $this->otp_code, OtpTypeEnum::RESET_PASSWORD))
            return null;

        $user = User::where('mobile', $this->username)->orWhere('email', $this->username)->firstOrFail();
        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        request()->session()->regenerate();

        return (new RedirectLoggedInUserAction())->execute();
    }
}
