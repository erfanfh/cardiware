<?php

namespace App\Http\Requests\Admin\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class RejectDrawWinnerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reason' => is_string($this->input('reason')) ? trim($this->input('reason')) : $this->input('reason')]);
    }
}
