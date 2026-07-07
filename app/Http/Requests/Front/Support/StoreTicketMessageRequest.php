<?php

namespace App\Http\Requests\Front\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketMessageRequest extends FormRequest
{
    protected $errorBag = 'replyTicket';

    public function authorize(): bool
    {
        return auth()->check();
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
            'message' => 'متن پیام',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'لطفاً متن پیام را وارد کنید.',
            'message.string' => 'متن پیام باید متن معتبر باشد.',
            'message.min' => 'متن پیام باید حداقل :min کاراکتر باشد.',
            'message.max' => 'متن پیام نباید بیشتر از :max کاراکتر باشد.',
        ];
    }
}
