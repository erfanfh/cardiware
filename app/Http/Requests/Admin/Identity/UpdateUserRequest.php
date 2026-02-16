<?php

namespace App\Http\Requests\Admin\Identity;

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
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

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
                'status' => ['required', 'boolean'],
            ];
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'boolean'],
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
            'role_id' => 'نقش',
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
            'mobile.regex' => 'موبایل باید به فرمت صحیح (09xxxxxxxxx) باشد.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
            'role_id.required' => 'لطفاً یک نقش انتخاب کنید.',
            'role_id.exists' => 'نقش انتخاب شده معتبر نیست.',
        ];
    }
}
