<?php

namespace App\Livewire\Front\Account;

use App\Application\Identity\Actions\Auth\ChangeAccountPasswordWithOtpAction;
use App\Application\Identity\Actions\Auth\RequestAccountPasswordChangeOtpAction;
use App\Application\Identity\Actions\Auth\UpdateUserPasswordAction;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Exceptions\InvalidOtpException;
use App\Application\Identity\Exceptions\PasswordUnchangedException;
use App\Domain\Identity\Models\User;
use App\Rules\ValidPassword;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Throwable;

class AccountSecuritySection extends Component
{
    public string $method = 'current_password';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $otp_code = '';

    public bool $otpSent = false;

    public int $otpResendAvailableAt = 0;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    /**
     * Save a new password after checking the current password.
     */
    public function save(UpdateUserPasswordAction $updateUserPasswordAction): void
    {
        $this->clearMessages();

        $validated = $this->validate($this->passwordRules(requireCurrentPassword: true), $this->messages());

        try {
            $user = $this->authenticatedUser();

            if (!Hash::check($validated['current_password'], $user->getAuthPassword())) {
                $this->addError('current_password', 'رمز عبور فعلی اشتباه است.');

                return;
            }

            if (Hash::check($validated['password'], $user->getAuthPassword())) {
                $this->addError('password', 'رمز عبور جدید نباید با رمز عبور فعلی یکسان باشد.');

                return;
            }

            $user = $updateUserPasswordAction->execute(
                user: $user,
                password: $validated['password'],
            );

            Auth::setUser($user);

            $this->resetSensitiveFields();
            $this->successMessage = 'رمز عبور شما با موفقیت تغییر کرد.';
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'تغییر رمز عبور با خطا مواجه شد. لطفاً دوباره تلاش کنید.';
        }
    }

    /**
     * Switch between current-password and OTP verification.
     */
    public function switchMethod(string $method): void
    {
        if (!in_array($method, ['current_password', 'otp'], true)) {
            return;
        }

        $this->method = $method;
        $this->otpSent = false;
        $this->otpResendAvailableAt = 0;

        $this->resetSensitiveFields();
        $this->clearMessages();
    }

    /**
     * Send a password-change OTP to the authenticated user's mobile.
     */
    public function requestOtp(RequestAccountPasswordChangeOtpAction $requestOtpAction): void
    {
        $this->sendOtp($requestOtpAction);
    }

    /**
     * Resend the password-change OTP when its cooldown has ended.
     */
    public function resendOtp(RequestAccountPasswordChangeOtpAction $requestOtpAction): void
    {
        $this->sendOtp($requestOtpAction);
    }

    /**
     * Save a new password after OTP verification.
     */
    public function saveWithOtp(ChangeAccountPasswordWithOtpAction $changePasswordAction): void
    {
        $this->clearMessages();

        if (!$this->otpSent) {
            $this->errorMessage = 'ابتدا کد تایید را برای شماره موبایل خود دریافت کنید.';

            return;
        }

        $this->otp_code = (string)normalize_number(trim($this->otp_code));

        $validated = $this->validate(
            array_merge(
                $this->passwordRules(requireCurrentPassword: false),
                ['otp_code' => ['required', 'digits:' . $this->otpLength()]],
            ),
            $this->messages(),
        );

        try {
            $user = $changePasswordAction->execute(
                authenticatedUser: $this->authenticatedUser(),
                otpCode: $validated['otp_code'],
                password: $validated['password'],
            );

            Auth::setUser($user);

            $this->method = 'current_password';
            $this->otpSent = false;
            $this->otpResendAvailableAt = 0;

            $this->resetSensitiveFields();
            $this->successMessage = 'رمز عبور شما با تایید شماره موبایل با موفقیت تغییر کرد.';
        } catch (InvalidOtpException $exception) {
            $this->addError('otp_code', $exception->getMessage());
        } catch (PasswordUnchangedException $exception) {
            $this->addError('password', $exception->getMessage());
        } catch (DomainException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'تغییر رمز عبور با خطا مواجه شد. لطفاً دوباره تلاش کنید.';
        }
    }

    public function render(): View
    {
        return view('livewire.front.account.account-security-section', [
            'maskedMobile' => $this->maskedMobile($this->authenticatedUser()->mobile),
            'otpLength' => $this->otpLength(),
        ]);
    }

    private function sendOtp(RequestAccountPasswordChangeOtpAction $requestOtpAction): void
    {
        $this->clearMessages();
        $this->resetErrorBag('otp_code');

        try {
            $result = $requestOtpAction->execute($this->authenticatedUser());
        } catch (DomainException $exception) {
            $this->errorMessage = $exception->getMessage();

            return;
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'ارسال کد تایید با خطا مواجه شد. لطفاً دوباره تلاش کنید.';

            return;
        }

        if (!$result->sent && !$result->alreadySent) {
            $this->errorMessage = $result->message;

            return;
        }

        $this->method = 'otp';
        $this->otpSent = true;
        $this->otp_code = '';
        $this->syncOtpTiming($result);
        $this->successMessage = $result->message;
    }

    private function syncOtpTiming(SendOtpResultDTO $result): void
    {
        $this->otpResendAvailableAt = now()
            ->addSeconds(max(0, $result->retryAfterSeconds))
            ->timestamp;

        $this->dispatch(
            'account-security-otp-timing-updated',
            resendAvailableAt: $this->otpResendAvailableAt,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function passwordRules(bool $requireCurrentPassword): array
    {
        $rules = [
            'password' => ['required', 'string', new ValidPassword, 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];

        if ($requireCurrentPassword) {
            $rules['current_password'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'current_password.required' => 'رمز عبور فعلی را وارد کنید.',
            'password.required' => 'رمز عبور جدید را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور جدید با رمز عبور جدید یکسان نیست.',
            'password_confirmation.required' => 'تکرار رمز عبور جدید را وارد کنید.',
            'otp_code.required' => 'کد تایید را وارد کنید.',
            'otp_code.digits' => 'کد تایید وارد شده معتبر نیست.',
        ];
    }

    private function resetSensitiveFields(): void
    {
        $this->reset([
            'current_password',
            'password',
            'password_confirmation',
            'otp_code',
        ]);

        $this->resetValidation();
    }

    private function clearMessages(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function otpLength(): int
    {
        return min(8, max(4, (int)config('auth-flow.otp.length', 6)));
    }

    private function maskedMobile(?string $mobile): string
    {
        $mobile = trim((string)$mobile);

        if (mb_strlen($mobile) <= 7) {
            return 'شماره ثبت‌شده حساب';
        }

        return mb_substr($mobile, 0, 4) . ' *** ' . mb_substr($mobile, -4);
    }
}
