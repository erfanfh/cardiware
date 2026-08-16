<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkEarningRuleActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('bulk_action')) {
            'activate', 'deactivate' => Gate::allows('loyalty-rule.edit'),
            'delete' => Gate::allows('loyalty-rule.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => ['required', 'string', Rule::in(DefaultBulkActions::set()->values())],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:earning_rules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفا یک عملیات دسته جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب شده معتبر نیست.',
            'ids.required' => 'لطفا حداقل یک قانون امتیاز را انتخاب کنید.',
            'ids.array' => 'فرمت قوانین انتخاب شده معتبر نیست.',
            'ids.min' => 'حداقل یک قانون امتیاز باید انتخاب شود.',
            'ids.*.integer' => 'شناسه قانون امتیاز نامعتبر است.',
            'ids.*.distinct' => 'شناسه های تکراری ارسال شده اند.',
            'ids.*.exists' => 'یکی از قوانین امتیاز انتخاب شده معتبر نیست.',
        ];
    }

    /** @return list<int> */
    public function earningRuleIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
