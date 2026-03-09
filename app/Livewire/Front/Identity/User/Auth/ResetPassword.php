<?php

namespace App\Livewire\Front\Identity\User\Auth;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
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


class ResetPassword extends Component
{
    public string $step = 'enter-username';
    public string $username = '';
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

    public function goToEnterUsername(): void
    {
        $this->resetValidation();
        $this->reset([
            'username',
            'first_name',
            'last_name',
            'password',
            'password_confirmation',
        ]);

        $this->step = 'enter-username';
    }


    /**
     * @return void
     * @throws RandomException
     */
    public function sendOtp(): void
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

        return redirect('/');
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
