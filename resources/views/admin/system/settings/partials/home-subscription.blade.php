<div class="space-y-6">
    <x-admin.metabox title="اشتراک ویژه" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان"
            >
                <x-admin.form.input
                    name="subscription_title"
                    :value="old('subscription_title', get_setting('home', 'subscription_title'))"
                />
            </x-admin.field>

            <x-admin.field label="زیرعنوان"
            >
                <x-admin.form.input
                    name="subscription_subtitle"
                    :value="old('subscription_subtitle', get_setting('home', 'subscription_subtitle'))"
                />
            </x-admin.field>

            <x-admin.field label="متن دکمه"
            >
                <x-admin.form.input
                    name="subscription_button_title"
                    :value="old('subscription_button_title', get_setting('home', 'subscription_button_title'))"
                />
            </x-admin.field>

            <x-admin.field label="لینک دکمه"
            >
                <x-admin.form.input
                    dir="ltr"
                    name="subscription_button_url"
                    :value="old('subscription_button_url', get_setting('home', 'subscription_button_url'))"
                />
            </x-admin.field>

            <x-admin.field label="توضیح کارت اشتراک ویژه (وسط کارت)"
                           hint="متن یا معرفی کامل اشتراک؛ چند خط پشتیبانی می‌شود. وسط کارت خالی نماند."
            >
                <x-admin.form.textarea
                    name="subscription_description"
                    rows="4"
                >{{ old('subscription_description', get_setting('home', 'subscription_description')) }}</x-admin.form.textarea>
            </x-admin.field>

            <x-admin.field label="تصویر کارت اشتراک ویژه (اختیاری)"
                           hint="اگر خالی باشد فقط متن توضیح نمایش داده می‌شود."
            >
                <x-admin.form.media-picker
                    mode="field"
                    name="subscription_image"
                    :value="[
                        'id' => old('subscription_image.id', get_setting('home', 'subscription_image')?->id),
                        'path' => old('subscription_image.path', get_setting('home', 'subscription_image')?->path),
                    ]"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
