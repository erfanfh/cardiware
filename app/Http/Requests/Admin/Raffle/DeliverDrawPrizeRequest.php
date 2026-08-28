<?php

namespace App\Http\Requests\Admin\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class DeliverDrawPrizeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['note' => ['nullable', 'string', 'max:4000']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['note' => is_string($this->input('note')) ? trim($this->input('note')) : $this->input('note')]);
    }
}
