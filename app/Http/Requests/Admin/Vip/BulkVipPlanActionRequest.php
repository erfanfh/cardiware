<?php

namespace App\Http\Requests\Admin\Vip;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkVipPlanActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('bulk_action')) {
            'activate', 'deactivate' => Gate::allows('vip-plan.edit'),
            'delete' => Gate::allows('vip-plan.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => ['required', 'string', Rule::in(DefaultBulkActions::set()->values())],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('vip_plans', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'لطفاً دست‌کم یک پلن را انتخاب کنید.',
            'ids.*.exists' => 'یکی از پلن‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    public function bulkAction(): string
    {
        return (string) $this->string('bulk_action')->value();
    }

    public function planIds(): array
    {
        return array_map('intval', $this->validated('ids'));
    }
}
