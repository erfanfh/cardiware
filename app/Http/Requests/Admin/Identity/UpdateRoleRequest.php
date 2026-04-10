<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for creating/updating a role
 */
class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'title')->ignore($roleId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'permissions' => collect($this->input('permissions'))
                ->filter()
                ->keys()
                ->toArray(),
        ]);
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان نقش',
            'permissions' => 'دسترسی‌ها',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان نقش الزامی است.',
            'title.max' => 'عنوان نقش نباید بیشتر از 255 کاراکتر باشد.',
            'title.unique' => 'این عنوان نقش قبلاً استفاده شده است.',
            'permissions.array' => 'دسترسی‌ها باید آرایه باشند.',
            'permissions.*.exists' => 'یکی از دسترسی‌های انتخاب شده معتبر نیست.',
        ];
    }
}
