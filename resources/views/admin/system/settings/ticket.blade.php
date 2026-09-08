@extends('admin.layout')

@section('title', 'تنظیمات تیکت')

@section('content')
    <x-admin.form action="{{ route('admin.settings.ticket.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="تصاویر پیوست تیکت">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <x-admin.field
                            label="حداکثر حجم هر تصویر"
                            hint="بین ۱ تا ۲۰ مگابایت"
                            :error="$errors->first('attachment_max_size_mb')"
                        >
                            <div class="flex items-center gap-2">
                                <x-admin.form.input
                                    name="attachment_max_size_mb"
                                    type="number"
                                    min="1"
                                    max="20"
                                    :value="old('attachment_max_size_mb', get_setting('ticket', 'attachment_max_size_mb', \App\Infrastructure\Services\Setting\Groups\TicketSettings::DEFAULT_ATTACHMENT_MAX_SIZE_MB))"
                                    required
                                />
                                <span class="shrink-0 text-xs font-bold text-slate-500">مگابایت</span>
                            </div>
                        </x-admin.field>

                        <x-admin.field
                            label="حداکثر تصاویر هر پیام"
                            hint="بین ۱ تا ۱۰ تصویر"
                            :error="$errors->first('attachments_per_message')"
                        >
                            <x-admin.form.input
                                name="attachments_per_message"
                                type="number"
                                min="1"
                                max="10"
                                :value="old('attachments_per_message', get_setting('ticket', 'attachments_per_message', \App\Infrastructure\Services\Setting\Groups\TicketSettings::DEFAULT_ATTACHMENTS_PER_MESSAGE))"
                                required
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="حذف خودکار تصاویر پس از"
                            hint="بین ۱ تا ۳۶۵۰ روز"
                            :error="$errors->first('attachment_retention_days')"
                        >
                            <div class="flex items-center gap-2">
                                <x-admin.form.input
                                    name="attachment_retention_days"
                                    type="number"
                                    min="1"
                                    max="3650"
                                    :value="old('attachment_retention_days', get_setting('ticket', 'attachment_retention_days', \App\Infrastructure\Services\Setting\Groups\TicketSettings::DEFAULT_ATTACHMENT_RETENTION_DAYS))"
                                    required
                                />
                                <span class="shrink-0 text-xs font-bold text-slate-500">روز</span>
                            </div>
                        </x-admin.field>
                    </div>

                    <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs font-bold leading-7 text-blue-700">
                        تصاویر کاربران در فضای خصوصی ذخیره می‌شوند و فقط صاحب تیکت و مدیر دارای دسترسی مشاهده تیکت قادر به دیدن آن‌ها هستند. پاک‌سازی خودکار هر روز اجرا می‌شود.
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="نکات ارسال تیکت">
                    <x-admin.field>
                        <x-admin.form.repeater
                            :items="get_setting('ticket', 'ticket_submission_tips')"
                            name="ticket_submission_tips"
                            title="نکته ها"
                            addText="افزودن نکته"
                            headingKey="text"
                            :defaultItem="[
                                'text' => '',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-4">
                                <x-admin.field label="متن">
                                    <x-admin.form.input x-model="it.text"
                                                        x-bind:name="field(i, 'text')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </x-admin.field>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
