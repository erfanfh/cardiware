<?php

namespace App\Http\Requests\Admin\Discussion;

use App\Domain\Discussion\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Comment|null $comment */
        $comment = $this->route('comment');

        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
            'rating_stars' => [
                Rule::requiredIf(fn() => $comment?->isRoot() === true),
                Rule::prohibitedIf(fn() => $comment?->isReply() === true),
                'nullable',
                'integer',
                'between:1,5',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'لطفاً متن دیدگاه را وارد کنید.',
            'content.min' => 'متن دیدگاه خیلی کوتاه است.',
            'content.max' => 'متن دیدگاه بیش از حد مجاز طولانی است.',
            'rating_stars.required' => 'انتخاب امتیاز برای دیدگاه اصلی الزامی است.',
            'rating_stars.integer' => 'امتیاز انتخاب‌شده معتبر نیست.',
            'rating_stars.between' => 'امتیاز باید بین ۱ تا ۵ ستاره باشد.',
            'rating_stars.prohibited' => 'پاسخ‌ها امتیاز مستقل ندارند.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $ratingStars = $this->input('rating_stars');

        $this->merge([
            'content' => trim((string)$this->input('content')),
            'rating_stars' => filled($ratingStars) ? (int)$ratingStars : null,
        ]);
    }
}
