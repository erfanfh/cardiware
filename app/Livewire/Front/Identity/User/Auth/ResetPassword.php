<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
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


class ResetPassword extends Component
{
    public string $step = 'enter-mobile';
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
        return view('livewire.front.identity.user.auth.reset-password');
    }

    public function goToEnterMobile(): void
    {
        $this->resetValidation();
        $this->reset([
            'mobile',
            'mobile',
            'email',
            'first_name',
            'last_name',
            'password',
            'password_confirmation',
        ]);

        $this->step = 'enter-mobile';
    }

    public function sendOtp(): void
    {
        $this->mobile = normalize_number($this->mobile);

        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید',
            'mobile.regex' => 'شماره موبایل معتبر نیست',
        ]);

        if (!User::where('mobile', $this->mobile)->exists()) {
            $this->addError('mobile', 'کاربری با این شماره موبایل ثبت نشده است');
            return;
        }

        $sent = (new GenerateOtpAction())->execute($this->mobile, OtpTypeEnum::RESET_PASSWORD);

        if (!$sent) {
            $this->addError('mobile', 'برای ارسال مجدد کد باید کمی صبر کنید');
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
        $this->mobile = normalize_number($this->mobile);
        $this->otp_code = normalize_number($this->otp_code);

        if ($this->rateLimit('request.forgot-password'))
            return null;

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
            'password_confirmation.required' => 'تکرار رمز عبور را وارد کنید',
            'password.min' => 'حداقل طول رمز عبور :min حرف می باشد',
            'password.string' => 'فرمت رمز عبور اشتباه می باشد',
            'password.confirmed' => 'رمزعبور و تکرار رمزعبور مطابقت ندارد',
        ]);

        if (!$this->validateOtp($this->mobile, $this->otp_code, OtpTypeEnum::RESET_PASSWORD))
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
     * @param string $mobile
     * @param string|int $code
     * @param OtpTypeEnum $type
     * @return bool
     */
    private function validateOtp(string $mobile, string|int $code, OtpTypeEnum $type): bool
    {
        $this->resetErrorBag();

        $otp = Otp::where('key', $mobile)
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
            if($user->mobile)
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
}
