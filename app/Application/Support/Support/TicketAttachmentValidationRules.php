<?php

namespace App\Application\Support\Support;

use App\Infrastructure\Services\Setting\Groups\TicketSettings;

class TicketAttachmentValidationRules
{
    public function __construct(
        private readonly TicketSettings $settings,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $maxFiles = $this->settings->attachmentsPerMessage();
        $maxKilobytes = $this->settings->attachmentMaxSizeMegabytes() * 1024;

        return [
            'attachments' => ['nullable', 'array', "max:{$maxFiles}"],
            'attachments.*' => [
                'bail',
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'extensions:jpg,jpeg,png,webp',
                "max:{$maxKilobytes}",
                'dimensions:min_width=1,min_height=1,max_width=8000,max_height=8000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxSizeMb = $this->settings->attachmentMaxSizeMegabytes();

        return [
            'attachments.array' => 'ساختار تصاویر پیوست معتبر نیست.',
            'attachments.max' => 'در هر پیام حداکثر :max تصویر می‌توانید ارسال کنید.',
            'attachments.*.required' => 'یکی از تصاویر انتخاب‌شده معتبر نیست.',
            'attachments.*.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'attachments.*.mimes' => 'فقط تصاویر JPG، PNG و WebP قابل ارسال هستند.',
            'attachments.*.mimetypes' => 'محتوای فایل باید یک تصویر معتبر JPG، PNG یا WebP باشد.',
            'attachments.*.extensions' => 'پسوند فایل باید JPG، JPEG، PNG یا WebP باشد.',
            'attachments.*.max' => "حجم هر تصویر نباید بیشتر از {$maxSizeMb} مگابایت باشد.",
            'attachments.*.dimensions' => 'ابعاد تصویر معتبر نیست یا بیش از حد مجاز است.',
        ];
    }
}
