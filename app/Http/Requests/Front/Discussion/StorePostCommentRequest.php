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
            'author_name' => is_string($this->author_name) ? trim($this->author_name) : $this->author_name,
            'author_email' => is_string($this->author_email) ? trim($this->author_email) : $this->author_email,
            'content' => is_string($this->content) ? trim($this->content) : $this->content,
            'rating_percent' => $this->rating_percent !== null && $this->rating_percent !== ''
                ? (int)$this->rating_percent
                : null,
            'parent_id' => $this->parent_id !== null && $this->parent_id !== ''
                ? (int)$this->parent_id
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
            'content.required' => 'متن دیدگاه الزامی است.',
            'content.min' => 'متن دیدگاه خیلی کوتاه است.',
            'content.max' => 'متن دیدگاه بیش از حد طولانی است.',
            'author_email.email' => 'ایمیل وارد شده معتبر نیست.',
            'rating_percent.min' => 'امتیاز نمی‌تواند کمتر از ۰ باشد.',
            'rating_percent.max' => 'امتیاز نمی‌تواند بیشتر از ۱۰۰ باشد.',
            'parent_id.exists' => 'دیدگاه والد معتبر نیست.',
        ];
    }
}
