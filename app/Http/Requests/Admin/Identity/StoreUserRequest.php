<?php

namespace App\Http\Requests\Admin\Identity;

use App\Domain\Identity\Enums\UserStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for creating a new user
 * Validates user data before creation
 */
class StoreUserRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'integer', Rule::exists('roles', 'id')],
            'mobile' => ['required', 'string', 'unique:users,mobile', 'regex:/^09[0-9]{9}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'sometimes', Rule::in([UserStatusEnum::ACTIVE, UserStatusEnum::INACTIVE])],
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
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
            'role.required' => 'نقش کاربر الزامی است.',
            'role.exists' => 'نقش انتخاب شده معتبر نیست.',
            'status.in' => 'نقش انتخاب شده معتبر نیست.',
        ];
    }
}
