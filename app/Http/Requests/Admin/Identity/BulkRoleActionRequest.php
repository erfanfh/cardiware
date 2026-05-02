<?php

namespace App\Http\Requests\Admin\Identity;

use App\Domain\Identity\Models\Role;
use App\Infrastructure\Support\BulkActions\DeleteBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkRoleActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('bulk_action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'delete' => Gate::allows('role.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => [
                'required',
                'string',
                Rule::in(DeleteBulkActions::set()->values()),
            ],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }

    /**
     * @param $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $defaultRoles = Role::whereIn('id', $this->input('ids'))
                ->where('is_default', true)
                ->orWhere('is_super_admin', true)
                ->exists();

            if ($defaultRoles) {
                $validator->errors()->add('ads', 'نقش های پیش‌فرض قابل حذف نیستند.');
            }
        });
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
