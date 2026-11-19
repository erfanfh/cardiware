<?php

namespace App\Http\Requests\Admin\Support;

use App\Domain\Support\Enums\TicketStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['nullable', 'integer'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'user_email' => ['nullable', 'string', 'max:255'],
            'user_mobile' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],

            'status' => [
                'nullable',
                'string',
                Rule::in(array_column(TicketStatusEnum::cases(), 'value')),
            ],

            'under_review' => ['nullable', 'integer', Rule::in([0, 1])],

            'department_id' => ['nullable', 'integer', 'exists:ticket_departments,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],

            'created_at_from' => ['nullable', 'string'],
            'created_at_to' => ['nullable', 'string'],
            'last_message_at_from' => ['nullable', 'string'],
            'last_message_at_to' => ['nullable', 'string'],

            'sort' => [
                'nullable',
                'string',
                Rule::in(['created_at', 'last_message_at', 'id']),
            ],

            'direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'number.integer' => 'شماره وارد شده معتبر نیست.',
            'user_name.string' => 'نام کاربر معتبر نیست.',
            'user_email.string' => 'ایمیل کاربر معتبر نیست.',
            'title.string' => 'عنوان تیکت معتبر نیست.',
            'message.string' => 'متن پیام معتبر نیست.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'department_id.exists' => 'دپارتمان انتخاب‌شده معتبر نیست.',
            'product_id.exists' => 'محصول انتخاب‌شده معتبر نیست.',
            'sort.in' => 'فیلد مرتب‌سازی معتبر نیست.',
            'direction.in' => 'جهت مرتب‌سازی معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'number' => $this->normalizeTicketNumber($this->input('number')),
            'created_at_from' => $this->normalizeDateToGregorian($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDateToGregorian($this->input('created_at_to')),
            'last_message_at_from' => $this->normalizeDateToGregorian($this->input('last_message_at_from')),
            'last_message_at_to' => $this->normalizeDateToGregorian($this->input('last_message_at_to')),
        ]);
    }

    protected function normalizeTicketNumber(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim(normalize_number($value));

        if (str_starts_with($value, '#')) {
            $value = trim(substr($value, 1));
        }

        return $value;
    }

    protected function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
