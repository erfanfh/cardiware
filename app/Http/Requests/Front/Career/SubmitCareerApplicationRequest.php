<?php

namespace App\Http\Requests\Front\Career;

use App\Domain\Career\Models\CareerPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitCareerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'career_position_id' => ['nullable', 'integer'],
            'preferred_cooperation_type' => ['nullable', Rule::in(['تمام‌وقت', 'پاره‌وقت', 'پروژه‌ای', 'منعطف'])],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:2048'],
            'skills_and_experience' => ['required', 'string', 'min:20', 'max:10000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'size:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $id = $this->input('career_position_id');

            if ($id && ! CareerPosition::publiclyAvailable()->whereKey($id)->exists()) {
                $validator->errors()->add('career_position_id', 'این موقعیت شغلی دیگر برای ارسال درخواست فعال نیست.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $phone = strtr((string) $this->input('phone', ''),
            ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        $this->merge([
            'phone' => preg_replace('/[\s-]+/u', '', $phone),
            'career_position_id' => $this->input('career_position_id') === 'general' ? null : $this->input('career_position_id'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'نام و نام خانوادگی',
            'phone' => 'شماره تماس',
            'email' => 'ایمیل',
            'career_position_id' => 'موقعیت مورد نظر',
            'preferred_cooperation_type' => 'نوع همکاری',
            'portfolio_url' => 'لینک رزومه یا نمونه‌کار',
            'skills_and_experience' => 'مهارت‌ها و سابقه',
            'resume' => 'فایل رزومه',
            'consent' => 'تأیید اطلاعات',
        ];
    }
}
