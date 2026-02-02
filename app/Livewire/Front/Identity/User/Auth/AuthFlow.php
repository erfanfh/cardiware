<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\System\RateLimiterAction;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Enums\RoleEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Component;

class AuthFlow extends Component
{
    public string $step = 'enter-mobile';
    public string $mobile = '';
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

    /**
     * @return void
     */
    public function submitMobile(): void
    {
        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید',
            'mobile.regex' => 'شماره موبایل خود را به طور صحیح وارد کنید',
        ]);

        $mobile = $this->mobile;

        if (User::where('mobile', $mobile)->exists()) {
            $this->step = 'login';
            return;
        }

        $this->step = 'register';
        session()->flash('success', 'کد تایید با موفقیت ارسال شد.');
    }

    /**
     * @return void
     */
    public function goToLoginByOtp(): void
    {
        $this->resetErrorBag();

        if (!User::where('mobile', $this->mobile)->exists()) {
            $this->addError('mobile', 'کاربری با این شماره موبایل ثبت نشده است.');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->mobile, OtpTypeEnum::LOGIN);

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

        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'mobile' => ['required'],
            'password' => ['required'],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید',
            'mobile.regex' => 'شماره موبایل اشتباه می باشد',
            'password.required' => 'رمز عبور را وارد کنید',
        ]);

        if (Auth::attempt(['mobile' => $this->mobile, 'password' => $this->password])) {
            $this->handleSuccessfulLogin();
            return redirect('/');
        }

        $this->addError('password', 'شماره موبایل یا رمز عبور اشتباه است.');
        return null;
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function verifyOtp(): RedirectResponse|Redirector|null
    {
        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'otp_code' => 'required',
        ], [
            'otp_code.required' => 'کد تایید را وارد کنید',
        ]);

        if (!$this->validateOtp($this->mobile, $this->otp_code, OtpTypeEnum::LOGIN)) {
            return null;
        }

        $user = User::where('mobile', $this->mobile)->firstOrFail();
        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        $this->handleSuccessfulLogin();

        return redirect('/');
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function register(): RedirectResponse|Redirector|null
    {
        if ($this->rateLimit('request.register'))
            return null;

        $this->validate([
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
            mobile: $this->mobile,
            password: $this->password
        );

        Auth::login($user);
        $this->handleSuccessfulLogin();

        return redirect('/');
    }

    /**
     * @return void
     */
    public function goToForgotPassword(): void
    {
        $this->resetErrorBag();

        if (!User::where('mobile', $this->mobile)->exists()) {
            $this->addError('mobile', 'کاربری با این شماره موبایل ثبت نشده است.');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->mobile, OtpTypeEnum::FORGOT_PASSWORD);

        if (!$sent) {
            $this->addError('otp_code', 'برای ارسال مجدد کد تایید باید 60 ثانیه صبر کنید');
            return;
        }

        $this->otpType = OtpTypeEnum::FORGOT_PASSWORD->value;
        $this->step = 'forgot-password';
        session()->flash('success', 'کد تایید با موفقیت ارسال شد.');
    }

    /**
     * @return RedirectResponse|Redirector|null
     */
    public function forgotPassword(): RedirectResponse|Redirector|null
    {
        if ($this->rateLimit('request.forgot-password'))
            return null;

        $this->mobile = $this->normalizeMobile($this->mobile);

        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'otp_code' => ['required'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => ['required'],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید',
            'mobile.regex' => 'شماره موبایل اشتباه می باشد',
            'otp_code.required' => 'کد تایید را وارد کنید',
            'password.required' => 'رمز عبور را وارد کنید',
            'password.min' => 'حداقل طول رمز عبور 6 حرف می باشد',
            'password.string' => 'فرمت رمز عبور اشتباه می باشد',
            'password.confirmed' => 'رمزعبور و تکرار رمزعبور مطابقت ندارد',
        ]);

        if (!$this->validateOtp($this->mobile, $this->otp_code, OtpTypeEnum::FORGOT_PASSWORD))
            return null;

        $user = User::where('mobile', $this->mobile)->firstOrFail();
        $user->update(['password' => Hash::make($this->password)]);

        Auth::login($user);
        $this->handleSuccessfulLogin();

        return redirect('/');
    }

    /**
     * @return void
     */
    public function resendOtp(): void
    {
        $this->resetErrorBag();

        if (!$this->mobile || !$this->otpType) {
            $this->addError('otp_code', 'شماره موبایل معتبر نیست');
            return;
        }

        $type = OtpTypeEnum::from($this->otpType);
        $sent = (new GenerateOtpAction())->execute($this->mobile, $type);

        if (!$sent) {
            $this->addError('otp_code', 'برای ارسال مجدد کد تایید باید 60 ثانیه صبر کنید');
            return;
        }

        session()->flash('success', 'کد تایید با موفقیت ارسال شد');
    }

    /**
     * @return void
     */
    public function resetToMobile(): void
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
        $this->step = 'enter-mobile';
    }

    /**
     * @param string $mobile
     * @param string|int $code
     * @param OtpTypeEnum $type
     * @return bool
     */
    private function validateOtp(string $mobile, string|int $code, OtpTypeEnum $type): bool
    {
        $this->resetErrorBag();

        $otp = Otp::where('mobile', $mobile)
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
     * @return void
     */
    private function handleSuccessfulLogin(): void
    {
        request()->session()->regenerate();

        $user = auth()->user();

        if ($user->role->title === RoleEnum::SUPER_ADMIN->value)
            (new SmsService())->send($user->mobile, 'ضیافت' . "\n" . 'ورود به پنل');
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
     * @param string|null $value
     * @return string|null
     */
    private function normalizeMobile(string|null $value): string|null
    {
        if ($value === null) {
            return null;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english,
            str_replace($arabic, $english, $value)
        );
    }
}
