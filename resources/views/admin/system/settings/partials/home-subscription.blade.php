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
        </div>
    </x-admin.metabox>
</div>
