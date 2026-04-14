<?php

namespace App\Http\Requests\Admin\Navigation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNavigationMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items_payload' => ['nullable', 'string'],
        ];
    }
}
