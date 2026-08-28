<?php

namespace App\Http\Requests\Admin\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

final class UpdateBackupSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('setting.backup');
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'files_enabled' => ['required', 'boolean'],
            'database_enabled' => ['required', 'boolean'],
            'full_interval_hours' => ['required', 'integer', 'min:6', 'max:168'],
            'database_interval_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'keep_all_days' => ['required', 'integer', 'min:1', 'max:30'],
            'keep_daily_days' => ['required', 'integer', 'min:7', 'max:180'],
            'keep_weekly_weeks' => ['required', 'integer', 'min:4', 'max:52'],
            'keep_monthly_months' => ['required', 'integer', 'min:3', 'max:36'],
            'keep_yearly_years' => ['required', 'integer', 'min:1', 'max:5'],
            'storage_limit_gb' => ['required', 'integer', 'min:10', 'max:145'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (!$this->boolean('files_enabled') && !$this->boolean('database_enabled')) {
                $validator->errors()->add('files_enabled', 'حداقل یکی از بک‌آپ فایل یا دیتابیس باید فعال باشد.');
            }
        }];
    }
}
