<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for updating an existing role
 * Validates role title and permissions data
 */
class UpdateRoleRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255', Rule::unique('roles', 'title')->ignore($roleId)],
            'permissions' => ['nullable', 'array', 'exists:permissions,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'permissions' => array_merge(array_filter($this->input('permissions'))),
        ]);
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
        ];
    }
}
