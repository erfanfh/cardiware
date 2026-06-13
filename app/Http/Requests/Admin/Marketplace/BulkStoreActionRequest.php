<?php

namespace App\Http\Requests\Admin\Marketplace;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkStoreActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('bulk_action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'activate', 'deactivate' => Gate::allows('store.edit'),
            'delete' => Gate::allows('store.delete'),
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
            'ids.*' => ['integer', 'distinct', 'exists:stores,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک فروشنده را انتخاب کنید.',
            'ids.array' => 'فرمت فروشنده‌های انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک فروشنده باید انتخاب شود.',
            'ids.*.integer' => 'شناسه فروشنده نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از فروشنده‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function storeIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
