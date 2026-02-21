<?php

namespace App\Http\Requests\Content\Page;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    /**
     * Shared rules between store and update.
     *
     * @return array<string, mixed>
     */
    protected function baseRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'tags'  => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['required', 'exists:users,id'],
        ];
    }

    /**
     * Optional: normalize inputs (e.g., trim).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'published_at' => $this->published_at
                ? gregorian_date($this->published_at)
                : now(),
            'author_id' => auth()->user()->id,
        ]);
    }

    /**
     * Shared validation messages (optional).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان صفحه',
            'excerpt' => 'خلاصه صفحه',
            'body' => 'متن صفحه',
            'published_at' => 'تاریخ انتشار',
        ];
    }
}
