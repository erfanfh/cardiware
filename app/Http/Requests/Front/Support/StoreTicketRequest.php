<?php

namespace App\Http\Requests\Front\Support;

use App\Application\Support\Actions\CanCreateTicketAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTicketRequest extends FormRequest
{
    protected $errorBag = 'createTicket';

    public function authorize(): bool
    {
        return auth()->check()
            && app(CanCreateTicketAction::class)->handle($this->user());
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان تیکت',
            'message' => 'متن پیام',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'لطفاً عنوان تیکت را وارد کنید.',
            'title.string' => 'عنوان تیکت باید متن معتبر باشد.',
            'title.min' => 'عنوان تیکت باید حداقل :min کاراکتر باشد.',
            'title.max' => 'عنوان تیکت نباید بیشتر از :max کاراکتر باشد.',

            'message.required' => 'لطفاً متن پیام را وارد کنید.',
            'message.string' => 'متن پیام باید متن معتبر باشد.',
            'message.min' => 'متن پیام باید حداقل :min کاراکتر باشد.',
            'message.max' => 'متن پیام نباید بیشتر از :max کاراکتر باشد.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput()
                ->with('ticket_access_denied', true)
        );
    }
}
