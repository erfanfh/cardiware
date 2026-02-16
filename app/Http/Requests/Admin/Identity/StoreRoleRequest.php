<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation request for creating a new role
 * Validates role title and permissions data
 */
class StoreRoleRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255', 'unique:roles,title'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
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
            'title' => 'عنوان نقش',
            'permissions' => 'دسترسی‌ها',
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
            'title.required' => 'عنوان نقش الزامی است.',
            'title.max' => 'عنوان نقش نباید بیشتر از 255 کاراکتر باشد.',
            'title.unique' => 'این عنوان نقش قبلاً استفاده شده است.',
            'permissions.array' => 'دسترسی‌ها باید آرایه باشند.',
            'permissions.*.integer' => 'شناسه دسترسی باید عدد باشد.',
            'permissions.*.exists' => 'دسترسی انتخاب شده معتبر نیست.',
        ];
    }
}
