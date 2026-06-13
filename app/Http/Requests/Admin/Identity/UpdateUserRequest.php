<?php

namespace App\Http\Requests\Admin\Identity;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Rules\MobileRule;
use App\Rules\ValidPassword;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation request for creating and updating users.
 *
 * Usage:
 * - store route  => admin.users.store
 * - update route => admin.users.update
 * - status ajax  => admin.users.update (only status field)
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Get validation rules.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        /**
         * Status toggle only.
         */
        $isStatusToggle = $this->has('status')
            && !$this->has('first_name')
            && !$this->has('email');

        if ($isStatusToggle) {
            return [
                'status' => [
                    'required',
                    Rule::in([
                        UserStatusEnum::ACTIVE,
                        UserStatusEnum::INACTIVE,
                    ]),
                ],
            ];
        }

        /**
         * Detect create / update.
         */
        $isCreate = blank($userId);

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],

            'username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],

            'role' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],

            'mobile' => [
                'required',
                'string',
                Rule::unique('users', 'mobile')->ignore($userId),
                new MobileRule(),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'password' => array_filter([
                $isCreate ? 'required' : 'nullable',
                'string',
                new ValidPassword(),
                'confirmed',
            ]),

            'status' => [
                'nullable',
                'sometimes',
                Rule::in([
                    UserStatusEnum::ACTIVE,
                    UserStatusEnum::INACTIVE,
                ]),
            ],

            'avatar_id' => [
                'nullable',
                'integer',
                'exists:media_files,id',
            ],
        ];
    }

    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'display_name' => 'نام نمایشی',
            'username' => 'نام کاربری',
            'role' => 'نقش',
            'mobile' => 'موبایل',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
            'status' => 'وضعیت',
            'avatar_id' => 'تصویر پروفایل',
        ];
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'نام الزامی است.',
            'last_name.required' => 'نام خانوادگی الزامی است.',

            'username.required' => 'نام کاربری الزامی است.',
            'username.min' => 'نام کاربری باید حداقل 3 کاراکتر باشد.',
            'username.max' => 'نام کاربری نباید بیشتر از 60 کاراکتر باشد.',
            'username.regex' => 'نام کاربری فقط می‌تواند شامل حروف انگلیسی، عدد، نقطه، خط تیره و آندرلاین باشد.',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است.',

            'mobile.required' => 'موبایل الزامی است.',
            'mobile.unique' => 'این موبایل قبلاً ثبت شده است.',

            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',

            'role.required' => 'نقش کاربر الزامی است.',
            'role.exists' => 'نقش انتخاب شده معتبر نیست.',

            'status.in' => 'وضعیت انتخاب شده معتبر نیست.',
        ];
    }

    /**
     * Prepare input before validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->filled('username')
                ? strtolower(trim($this->input('username')))
                : null,

            'avatar_id' => $this->input('avatar.id'),
        ]);
    }
}
