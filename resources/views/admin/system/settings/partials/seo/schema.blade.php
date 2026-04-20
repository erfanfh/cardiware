<div class="space-y-6">
    <x-admin.metabox title="اطلاعات سازمان" :collapsible="false">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <x-admin.field label="نام سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_name"
                    :value="old('organization_name', get_setting('seo', 'organization_name'))"
                />
            </x-admin.field>

            <x-admin.field label="نام جایگزین سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_alternate_name"
                    :value="old('organization_alternate_name', get_setting('seo', 'organization_alternate_name'))"
                />
            </x-admin.field>

            <x-admin.field label="ایمیل سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_email"
                    class="direction-ltr"
                    :value="old('organization_email', get_setting('seo', 'organization_email'))"
                />
            </x-admin.field>

            <x-admin.field label="تلفن سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_phone"
                    class="direction-ltr"
                    :value="old('organization_phone', get_setting('seo', 'organization_phone'))"
                />
            </x-admin.field>

            <x-admin.field label="آدرس اصلی سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_url"
                    class="direction-ltr"
                    :value="old('organization_url', get_setting('seo', 'organization_url'))"
                />
            </x-admin.field>

            <x-admin.field label="نام قانونی سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_legal_name"
                    :value="old('organization_legal_name', get_setting('seo', 'organization_legal_name'))"
                />
            </x-admin.field>

            <x-admin.field label="لینک‌های شبکه‌های اجتماعی و مراجع رسمی" class="col-span-8">
                <x-admin.form.textarea
                    name="organization_same_as"
                    rows="6"
                    placeholder="در هر خط یک آدرس وارد کنید"
                >{{ old('organization_same_as', get_setting('seo', 'organization_same_as')) }}</x-admin.form.textarea>
            </x-admin.field>

            <x-admin.field label="لوگوی سازمان" class="col-span-4">
                <x-admin.form.media-picker
                    mode="field"
                    name="organization_logo"
                    :value="[
                        'id' => old('organization_logo.id', get_setting('seo', 'organization_logo')->id),
                        'path' => old('organization_logo.path', get_setting('seo', 'organization_logo')->path),
                    ]"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
