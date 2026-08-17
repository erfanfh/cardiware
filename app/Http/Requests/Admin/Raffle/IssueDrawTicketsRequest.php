<?php

namespace App\Http\Requests\Admin\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class IssueDrawTicketsRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],
            'quantity' => ['required','integer','min:1','max:1000'],
            'reason' => ['required','string','min:3','max:500'],
            'idempotency_key' => ['required','string','max:160'],
        ];
    }
}
