<?php

namespace App\Http\Requests\Admin\Identity;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkRoleActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulk_action' => [
                'required',
                'string',
                Rule::in(DefaultBulkActions::set()->values()),
            ],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک نقش را انتخاب کنید.',
            'ids.array' => 'فرمت نقش‌های انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک نقش باید انتخاب شود.',
            'ids.*.integer' => 'شناسه نقش نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از نقش‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function roleIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
