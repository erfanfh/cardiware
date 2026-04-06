<?php

namespace App\Http\Requests\Front\Discussion;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for storing a post comment.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_email' => ['nullable', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:2', 'max:10000'],
            'rating_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_name' => is_string($this->input('author_name'))
                ? trim($this->input('author_name'))
                : $this->input('author_name'),

            'author_email' => is_string($this->input('author_email'))
                ? trim($this->input('author_email'))
                : $this->input('author_email'),

            'content' => is_string($this->input('content'))
                ? trim($this->input('content'))
                : $this->input('content'),

            'rating_percent' => $this->input('rating_percent') !== null && $this->input('rating_percent') !== ''
                ? (int)$this->input('rating_percent')
                : null,

            'parent_id' => $this->input('parent_id') !== null && $this->input('parent_id') !== ''
                ? (int)$this->input('parent_id')
                : null,
        ]);
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'لطفا متن دیدگاه را وارد نمایید.',
            'content.min' => 'متن دیدگاه خیلی کوتاه است.',
            'content.max' => 'متن دیدگاه بیش از حد طولانی است.',
            'author_email.email' => 'ایمیل وارد شده معتبر نیست.',
            'rating_percent.min' => 'امتیاز نمی‌تواند کمتر از :min باشد.',
            'rating_percent.max' => 'امتیاز نمی‌تواند بیشتر از :max باشد.',
            'parent_id.exists' => 'دیدگاه والد معتبر نیست.',
        ];
    }
}
