<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'job_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'picture_id' => [
                'nullable',
                'integer',
                Rule::exists('media_files', 'id'),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:30',
            ],

            'website_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'job_title' => 'عنوان شغلی',
            'bio' => 'معرفی کوتاه',
            'picture_id' => 'تصویر',
            'email' => 'ایمیل',
            'mobile' => 'شماره موبایل',
            'website_url' => 'آدرس وب‌سایت',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->normalizeString(
                $this->input('first_name')
            ),

            'last_name' => $this->normalizeString(
                $this->input('last_name')
            ),

            'job_title' => $this->normalizeNullableString(
                $this->input('job_title')
            ),

            'bio' => $this->normalizeNullableString(
                $this->input('bio')
            ),

            'email' => $this->normalizeNullableString(
                $this->input('email')
            ),

            'mobile' => $this->normalizeNullableString(
                $this->input('mobile')
            ),

            'website_url' => $this->normalizeNullableString(
                $this->input('website_url')
            ),

            'picture_id' => $this->input('picture.id'),
        ]);
    }

    private function normalizeString(mixed $value): mixed
    {
        return is_string($value)
            ? trim($value)
            : $value;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
