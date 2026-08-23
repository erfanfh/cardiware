<?php

namespace App\Http\Requests\Front\Poll;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPollVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['option_ids' => ['required', 'array', 'min:1', 'max:100'], 'option_ids.*' => ['required', 'integer', 'distinct']];
    }

    public function attributes(): array
    {
        return ['option_ids' => 'گزینه انتخابی'];
    }
}
