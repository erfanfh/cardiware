<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustAudienceMemberPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::enum(PointEntryDirectionEnum::class)],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'note' => ['required', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'max:160'],
        ];
    }

    public function attributes(): array
    {
        return [
            'direction' => 'نوع تغییر',
            'amount' => 'مقدار امتیاز',
            'note' => 'دلیل تغییر',
            'idempotency_key' => 'شناسه عملیات',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => is_string($this->input('note')) ? trim($this->input('note')) : $this->input('note'),
        ]);
    }
}
