<?php

namespace App\Http\Requests\Admin\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class RetryDrawRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
