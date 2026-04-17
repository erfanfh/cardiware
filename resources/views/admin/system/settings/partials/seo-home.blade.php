<div class="space-y-6">
    <x-admin.metabox title="تنظیمات" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان صفحه اصلی">
                <x-admin.form.input
                    name="home_title"
                    :value="old('home_title', get_setting('seo', 'home_title'))"
                />
            </x-admin.field>

            <x-admin.field label="توضیحات صفحه اصلی">
                <x-admin.form.textarea
                    name="home_description"
                    rows="4"
                    :value="old('home_description', get_setting('seo', 'home_description'))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
