<?php

namespace App\Http\Requests\Front\Support;

use App\Application\Support\Support\TicketAttachmentValidationRules;
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
        ] + app(TicketAttachmentValidationRules::class)->rules();
    }

    public function attributes(): array
    {
        return [
            'message' => 'متن پیام',
            'attachments' => 'تصاویر پیوست',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'لطفاً متن پیام را وارد کنید.',
            'message.string' => 'متن پیام باید متن معتبر باشد.',
            'message.min' => 'متن پیام باید حداقل :min کاراکتر باشد.',
            'message.max' => 'متن پیام نباید بیشتر از :max کاراکتر باشد.',
        ] + app(TicketAttachmentValidationRules::class)->messages();
    }
}
