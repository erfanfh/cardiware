<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Application\Raffle\Listings\DrawTicketListing;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDrawTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => ['nullable','string','max:64'], 'status' => ['nullable', Rule::enum(DrawTicketStatusEnum::class)],
            'user_id' => ['nullable','integer','min:1'], 'user_query' => ['nullable','string','max:255'], 'grant_id' => ['nullable','integer','min:1'],
            'issued_at_from' => ['nullable','date'], 'issued_at_to' => ['nullable','date','after_or_equal:issued_at_from'],
            'sort' => ['nullable', Rule::in(array_keys(app(DrawTicketListing::class)->sortable()))], 'direction' => ['nullable', Rule::in(['asc','desc'])],
        ];
    }
    protected function prepareForValidation(): void
    {
        foreach (['issued_at_from','issued_at_to'] as $key) if ($this->filled($key)) $this->merge([$key => gregorian_date($this->input($key))]);
    }
}
