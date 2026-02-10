<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for assigning new role to users
 * Validates that new role is valid and different from current role
 */
class AssignNewRoleRequest extends FormRequest
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
        $roleId = $this->route('role')->id;

        return [
            'new_role_id' => ['required', 'integer', Rule::exists('roles', 'id'),
                Rule::notIn([$roleId])] // Cannot assign same role
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
            'new_role_id' => 'نقش جدید',
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
            'new_role_id.required' => 'لطفاً یک نقش جدید انتخاب کنید.',
            'new_role_id.exists' => 'نقش انتخاب‌شده معتبر نیست.',
            'new_role_id.not_in' => 'نمی‌توانید همان نقش فعلی را انتخاب کنید.',
        ];
    }
}
