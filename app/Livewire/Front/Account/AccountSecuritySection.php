<?php

namespace App\Livewire\Front\Account;

use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Throwable;

class AccountSecuritySection extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'current_password.required' => 'رمز عبور فعلی را وارد کنید.',

            'password.required' => 'رمز عبور جدید را وارد کنید.',
            'password.min' => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور جدید با رمز عبور جدید یکسان نیست.',

            'password_confirmation.required' => 'تکرار رمز عبور جدید را وارد کنید.',
        ];
    }

    /**
     * Save new password.
     */
    public function save(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $validated = $this->validate();

        try {
            /** @var User $user */
            $user = auth()->user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                $this->addError('current_password', 'رمز عبور فعلی اشتباه است.');
                return;
            }

            if (Hash::check($validated['password'], $user->password)) {
                $this->addError('password', 'رمز عبور جدید نباید با رمز عبور فعلی یکسان باشد.');
                return;
            }

            $user->forceFill([
                'password' => $validated['password'],
            ])->save();

            $this->reset([
                'current_password',
                'password',
                'password_confirmation',
            ]);

            $this->resetValidation();

            $this->successMessage = 'رمز عبور شما با موفقیت تغییر کرد.';
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'تغییر رمز عبور با خطا مواجه شد. لطفا دوباره تلاش کنید.';
        }
    }

    public function render(): View
    {
        return view('livewire.front.account.account-security-section');
    }
}
