<?php

namespace App\Http\Requests\Admin\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderDescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'توضیحات',
        ];
    }
}
