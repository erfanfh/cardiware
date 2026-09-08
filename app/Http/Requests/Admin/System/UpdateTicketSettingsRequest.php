<?php

namespace App\Http\Requests\Admin\System;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ticket_submission_tips' => ['nullable', 'array', 'max:20'],
            'ticket_submission_tips.*.text' => ['nullable', 'string', 'max:500'],
            'attachment_max_size_mb' => ['required', 'integer', 'min:1', 'max:20'],
            'attachments_per_message' => ['required', 'integer', 'min:1', 'max:10'],
            'attachment_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ];
    }

    public function attributes(): array
    {
        return [
            'attachment_max_size_mb' => 'حداکثر حجم هر تصویر',
            'attachments_per_message' => 'حداکثر تصاویر هر پیام',
            'attachment_retention_days' => 'مدت نگهداری تصاویر',
        ];
    }
}
