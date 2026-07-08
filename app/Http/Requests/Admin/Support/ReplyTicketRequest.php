<?php

namespace App\Http\Requests\Admin\Support;

use Illuminate\Foundation\Http\FormRequest;

class ReplyTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'message' => 'متن پاسخ',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'لطفاً متن پاسخ را وارد کنید.',
            'message.min' => 'متن پاسخ باید حداقل :min کاراکتر باشد.',
            'message.max' => 'متن پاسخ نباید بیشتر از :max کاراکتر باشد.',
        ];
    }
}
