<?php

namespace App\Http\Requests\Admin\Identity;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkUserActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('bulk_action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'activate', 'deactivate' => Gate::allows('user.edit'),
            'delete' => Gate::allows('user.delete'),
            default => false,
        };
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
            'ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک کاربر را انتخاب کنید.',
            'ids.array' => 'فرمت کاربر‌های انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک کاربر باید انتخاب شود.',
            'ids.*.integer' => 'شناسه کاربر نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از کاربر‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function userIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
