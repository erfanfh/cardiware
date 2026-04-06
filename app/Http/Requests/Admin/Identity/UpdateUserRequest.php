<?php

namespace App\Http\Requests\Admin\Identity;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Rules\MobileRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for updating user
 * Validates user status toggle and other updatable fields
 */
class UpdateUserRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        // Check if this is a status toggle or full update
        $isStatusToggle = $this->has('status') && !$this->has('first_name');

        if ($isStatusToggle) {
            // Only status update (from index page)
            return [
                'status' => ['required', Rule::in([UserStatusEnum::ACTIVE, UserStatusEnum::INACTIVE])],
            ];
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'integer', Rule::exists('roles', 'id')],
            'mobile' => ['required', 'string', Rule::unique('users', 'mobile')->ignore($userId), new MobileRule()],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in([UserStatusEnum::ACTIVE, UserStatusEnum::INACTIVE])],
            'avatar_id' => ['nullable', 'integer', 'exists:media_files,id'],
        ];
    }

    /**
     * Get custom attribute names for validator errors
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'display_name' => 'نام نمایشی',
            'role' => 'نقش',
            'mobile' => 'موبایل',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
            'status' => 'وضعیت',
        ];
    }

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'نام الزامی است.',
            'last_name.required' => 'نام خانوادگی الزامی است.',
            'mobile.required' => 'موبایل الزامی است.',
            'mobile.unique' => 'این موبایل قبلاً ثبت شده است.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
            'role.required' => 'نقش کاربر الزامی است.',
            'role.exists' => 'نقش انتخاب شده معتبر نیست.',
            'status.required' => 'وضعیت کاربر الزامی است.',
            'status.in' => 'نقش انتخاب شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'avatar_id' => $this->input('avatar.id'),
        ]);
    }
}
