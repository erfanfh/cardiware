<?php

namespace App\Http\Requests\Admin\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulk_action' => ['required', 'string', Rule::in(['close', 'reopen'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tickets,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً عملیات گروهی را انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک تیکت را انتخاب کنید.',
            'ids.array' => 'تیکت‌های انتخاب‌شده معتبر نیستند.',
            'ids.*.exists' => 'یکی از تیکت‌های انتخاب‌شده معتبر نیست.',
        ];
    }
}
