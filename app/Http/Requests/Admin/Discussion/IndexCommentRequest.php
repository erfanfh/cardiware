<?php

namespace App\Http\Requests\Admin\Discussion;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_email' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'content' => ['nullable', 'string', 'max:5000'],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_column(CommentStatusEnum::cases(), 'value')),
            ],
            'type' => [
                'nullable',
                'string',
                Rule::in(['root', 'reply']),
            ],
            'created_at_from' => ['nullable', 'string'],
            'created_at_to' => ['nullable', 'string'],
            'sort' => [
                'nullable',
                'string',
                Rule::in(['created_at', 'id']),
            ],
            'direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'commentable_type' => ['nullable', 'string', 'in:post,product'],
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.integer' => 'شناسه وارد شده معتبر نیست.',
            'author_name.string' => 'نام نویسنده معتبر نیست.',
            'author_email.string' => 'ایمیل نویسنده معتبر نیست.',
            'ip_address.string' => 'آی‌پی معتبر نیست.',
            'content.string' => 'متن دیدگاه معتبر نیست.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'type.in' => 'نوع دیدگاه انتخاب‌شده معتبر نیست.',
            'sort.in' => 'فیلد مرتب‌سازی معتبر نیست.',
            'direction.in' => 'جهت مرتب‌سازی معتبر نیست.',
            'post_id.exists' => 'شناسه نوشته وارد شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_at_from' => $this->normalizeDateToGregorian($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDateToGregorian($this->input('created_at_to')),
        ]);
    }

    protected function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
