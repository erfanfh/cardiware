<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Application\Raffle\Listings\DrawParticipantListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDrawParticipantRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id' => ['nullable','integer','min:1'], 'query' => ['nullable','string','max:255'],
            'active_tickets_from' => ['nullable','integer','min:0'], 'active_tickets_to' => ['nullable','integer','min:0','gte:active_tickets_from'],
            'sort' => ['nullable', Rule::in(array_keys(app(DrawParticipantListing::class)->sortable()))], 'direction' => ['nullable', Rule::in(['asc','desc'])],
        ];
    }
}
