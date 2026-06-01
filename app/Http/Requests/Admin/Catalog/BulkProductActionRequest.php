<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkProductActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('bulk_action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'activate', 'deactivate' => Gate::allows('product.edit'),
            'delete' => Gate::allows('product.delete'),
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
            'ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک محصول را انتخاب کنید.',
            'ids.array' => 'فرمت محصولات انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک محصول باید انتخاب شود.',
            'ids.*.integer' => 'شناسه محصول نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از محصولات انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
