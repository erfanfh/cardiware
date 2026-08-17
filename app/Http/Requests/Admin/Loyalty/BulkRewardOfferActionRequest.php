<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkRewardOfferActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('bulk_action')) {
            'activate', 'deactivate' => Gate::allows('loyalty-reward.edit'),
            'delete' => Gate::allows('loyalty-reward.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => ['required', 'string', Rule::in(DefaultBulkActions::set()->values())],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('reward_offers', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفا یک عملیات دسته جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب شده معتبر نیست.',
            'ids.required' => 'لطفا حداقل یک پاداش را انتخاب کنید.',
            'ids.min' => 'حداقل یک پاداش باید انتخاب شود.',
            'ids.*.exists' => 'یکی از پاداش‌های انتخاب شده معتبر نیست.',
        ];
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }

    /** @return list<int> */
    public function rewardOfferIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }
}
