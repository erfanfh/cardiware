<?php

namespace App\Http\Requests\Admin\Discussion;

use App\Infrastructure\Support\BulkActions\CommentBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkCommentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('bulk_action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'approve', 'pending', 'reject' => Gate::allows('comment.edit'),
            'delete' => Gate::allows('comment.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => [
                'required',
                'string',
                Rule::in(CommentBulkActions::set()->values()),
            ],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:comments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulk_action.required' => 'لطفاً یک عملیات دسته‌جمعی انتخاب کنید.',
            'bulk_action.in' => 'عملیات انتخاب‌شده معتبر نیست.',
            'ids.required' => 'لطفاً حداقل یک دیدگاه را انتخاب کنید.',
            'ids.array' => 'فرمت دیدگاه‌های انتخاب‌شده معتبر نیست.',
            'ids.min' => 'حداقل یک دیدگاه باید انتخاب شود.',
            'ids.*.integer' => 'شناسه دیدگاه نامعتبر است.',
            'ids.*.distinct' => 'شناسه‌های تکراری ارسال شده‌اند.',
            'ids.*.exists' => 'یکی از دیدگاه‌های انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return list<int>
     */
    public function commentIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string)$this->string('bulk_action')->value();
    }
}
