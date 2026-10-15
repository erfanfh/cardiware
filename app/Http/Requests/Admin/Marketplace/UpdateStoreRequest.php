<?php

namespace App\Http\Requests\Admin\Marketplace;

use App\Domain\Marketplace\Enums\StoreStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'status' => ['required', Rule::enum(StoreStatusEnum::class)],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'body' => ['nullable', 'string'],
            'activity_field' => ['nullable', 'string'],
            'hide_sales_count' => ['sometimes', 'boolean'],

            'seller_user_ids' => ['sometimes', 'array'],
            'seller_user_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->whereNull('deleted_at')],

            'seo' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان',
            'slug' => 'اسلاگ',
            'status' => 'وضعیت',
            'body' => 'توضیحات',
            'hide_sales_count' => 'عدم نمایش تعداد فروش',
            'seller_user_ids' => 'فروشندگان',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('seller_user_ids_submitted') && !$this->has('seller_user_ids')) {
            $this->merge(['seller_user_ids' => []]);
        }

        $this->merge([
            'picture_id' => $this->input('picture.id'),
        ]);
    }
}
