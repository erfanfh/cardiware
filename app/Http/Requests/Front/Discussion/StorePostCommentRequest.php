<?php

namespace App\Http\Requests\Front\Discussion;

use App\Rules\CommentSubmissionCooldown;
use App\Rules\UniqueCommentPerModel;
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
        $post = $this->route('post');
        $user = $this->user();
        $isReply = $this->filled('parent_id');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'author_email' => ['nullable', 'email', 'max:190'],
            'content' => [
                'required',
                'string',
                'min:2',
                'max:5000',
                new UniqueCommentPerModel(
                    commentableType: $post->getMorphClass(),
                    commentableId: $post->getKey(),
                    userId: $user?->id,
                    authorEmail: $user ? null : $this->input('author_email'),
                    parentId: $this->input('parent_id'),
                ),
                new CommentSubmissionCooldown(
                    commentableType: $post->getMorphClass(),
                    commentableId: $post->getKey(),
                    userId: $user?->id,
                    authorEmail: $user ? null : $this->input('author_email'),
                    seconds: 60,
                ),
            ],
            'rating_percent' => $isReply
                ? ['nullable']
                : ['nullable', 'integer', 'min:0', 'max:100'],
            'website' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'integer'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $authorName = $this->input('author_name');
        $authorEmail = $this->input('author_email');
        $content = $this->input('content');
        $ratingPercent = $this->input('rating_percent');
        $parentId = $this->input('parent_id');
        $website = $this->input('website');
        $startedAt = $this->input('started_at');

        $this->merge([
            'author_name' => is_string($authorName) ? trim($authorName) : $authorName,
            'author_email' => is_string($authorEmail) ? trim($authorEmail) : $authorEmail,
            'content' => is_string($content) ? trim($content) : $content,
            'rating_percent' => $ratingPercent !== null && $ratingPercent !== ''
                ? (int)$ratingPercent
                : null,
            'parent_id' => $parentId !== null && $parentId !== ''
                ? (int)$parentId
                : null,
            'website' => is_string($website) ? trim($website) : $website,
            'started_at' => $startedAt !== null && $startedAt !== ''
                ? (int)$startedAt
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateHoneypot($validator);
            $this->validateMinimumSubmitTime($validator);
        });
    }

    protected function validateHoneypot($validator): void
    {
        if (filled($this->input('website'))) {
            $validator->errors()->add('spam', 'درخواست نامعتبر است.');
        }
    }

    protected function validateMinimumSubmitTime($validator): void
    {
        $startedAt = $this->input('started_at');

        if (!$startedAt) {
            $validator->errors()->add('spam', 'درخواست نامعتبر است.');
            return;
        }

        $elapsedSeconds = now()->timestamp - (int)$startedAt;

        if ($elapsedSeconds < 3) {
            $validator->errors()->add('spam', 'ارسال فرم خیلی سریع انجام شد. لطفاً دوباره تلاش کنید.');
        }

        if ($elapsedSeconds > 60 * 60 * 12) {
            $validator->errors()->add('spam', 'فرم منقضی شده است. لطفاً صفحه را تازه‌سازی کنید.');
        }
    }
}
