<?php

namespace App\Http\Requests\Admin\Discussion;

use Illuminate\Foundation\Http\FormRequest;

class ReplyToCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'لطفاً متن پاسخ را وارد کنید.',
            'content.min' => 'متن پاسخ خیلی کوتاه است.',
            'content.max' => 'متن پاسخ بیش از حد مجاز طولانی است.',
        ];
    }

    public function content(): string
    {
        return trim((string)$this->input('content'));
    }
}
