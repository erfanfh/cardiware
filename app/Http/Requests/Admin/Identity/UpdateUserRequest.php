<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'status' => ['required', 'boolean'],
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
            'status.required' => 'وضعیت کاربر الزامی است.',
            'status.boolean' => 'وضعیت کاربر باید معتبر باشد.',
        ];
    }
}
