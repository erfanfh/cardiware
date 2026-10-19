<x-admin.metabox title="گزارش روزانه وضعیت سیستم">
    <p class="mb-5 text-sm leading-7 text-slate-600">
        هر روز ساعت ۲۰:۰۰ به وقت تهران، خلاصه نیاز به رسیدگی امنیتی پکیج‌های Composer، وجود آسیب‌پذیری Critical در npm و وضعیت بک‌آپ‌های ۲۴ ساعت گذشته ایمیل می‌شود.
    </p>
    <x-admin.field label="ایمیل گیرندگان" :error="$errors->first('system_report_recipients')" hint="هر ایمیل را در یک خط وارد کنید (حداکثر ۲۰ ایمیل). با خالی گذاشتن این فیلد، ارسال گزارش غیرفعال می‌شود.">
        <x-admin.form.textarea
            name="system_report_recipients"
            dir="ltr"
            :value="old('system_report_recipients', get_setting('general', 'system_report_recipients'))"
            placeholder="admin@example.com"
        />
    </x-admin.field>
</x-admin.metabox>
