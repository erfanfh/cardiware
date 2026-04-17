<div class="space-y-6">
    <x-admin.metabox title="اطلاعات سازمان" :collapsible="false">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-admin.field label="نام سازمان">
                <x-admin.form.input
                    name="organization_name"
                    :value="old('organization_name', get_setting('seo', 'organization_name'))"
                />
            </x-admin.field>

            <x-admin.field label="نام جایگزین سازمان">
                <x-admin.form.input
                    name="organization_alternate_name"
                    :value="old('organization_alternate_name', get_setting('seo', 'organization_alternate_name'))"
                />
            </x-admin.field>

            <x-admin.field label="آدرس اصلی سازمان">
                <x-admin.form.input
                    name="organization_url"
                    class="direction-ltr"
                    :value="old('organization_url', get_setting('seo', 'organization_url'))"
                />
            </x-admin.field>

            <x-admin.field label="لوگوی سازمان">
                <x-admin.form.input
                    name="organization_logo"
                    class="direction-ltr"
                    :value="old('organization_logo', get_setting('seo', 'organization_logo'))"
                />
            </x-admin.field>

            <x-admin.field label="ایمیل سازمان">
                <x-admin.form.input
                    name="organization_email"
                    class="direction-ltr"
                    :value="old('organization_email', get_setting('seo', 'organization_email'))"
                />
            </x-admin.field>

            <x-admin.field label="تلفن سازمان">
                <x-admin.form.input
                    name="organization_phone"
                    class="direction-ltr"
                    :value="old('organization_phone', get_setting('seo', 'organization_phone'))"
                />
            </x-admin.field>

            <div class="lg:col-span-2">
                <x-admin.field label="لینک‌های شبکه‌های اجتماعی و مراجع رسمی">
                    <x-admin.form.textarea
                        name="organization_same_as"
                        rows="5"
                        placeholder="در هر خط یک آدرس وارد کنید"
                    >{{ old('organization_same_as', get_setting('seo', 'organization_same_as')) }}</x-admin.form.textarea>
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>
</div>
