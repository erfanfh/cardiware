<?php

namespace App\Http\Requests\Admin\Project;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkProjectActionRequest extends FormRequest
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
            'ids.*' => ['integer', 'distinct', 'exists:projects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک پروژه را انتخاب کنید.',
            'ids.array' => 'فرمت پروژه‌های انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک پروژه باید انتخاب شود.',
            'ids.*.integer' => 'شناسه پروژه نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از پروژه‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function projectIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
