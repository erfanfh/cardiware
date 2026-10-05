<?php

namespace App\Http\Requests\Admin\Vip;

use App\Application\Vip\Listings\VipPlanListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $durationMaxRules = ['nullable', 'integer', 'min:1'];
        $priceMaxRules = ['nullable', 'integer', 'min:0'];
        $createdToRules = ['nullable', 'date'];

        if ($this->filled('duration_days_min')) {
            $durationMaxRules[] = 'gte:duration_days_min';
        }

        if ($this->filled('price_toman_min')) {
            $priceMaxRules[] = 'gte:price_toman_min';
        }

        if ($this->filled('created_at_from')) {
            $createdToRules[] = 'after_or_equal:created_at_from';
        }

        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'duration_days_min' => ['nullable', 'integer', 'min:1'],
            'duration_days_max' => $durationMaxRules,
            'price_toman_min' => ['nullable', 'integer', 'min:0'],
            'price_toman_max' => $priceMaxRules,
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => $createdToRules,
            'sort' => ['nullable', 'string', Rule::in(array_keys(app(VipPlanListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_at_from' => $this->normalizeDate($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDate($this->input('created_at_to')),
        ]);
    }

    private function normalizeDate(mixed $value): mixed
    {
        return is_string($value) && trim($value) !== '' ? gregorian_date($value) : null;
    }
}
