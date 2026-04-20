<div class="space-y-6">
    <x-admin.metabox title="اطلاعات پایه" :collapsible="false">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <x-admin.field label="زبان" class="col-span-6">
                <x-admin.form.select
                    name="schema_language"
                    :value="old('schema_language', get_setting('seo', 'schema_language'))"
                    :options="[
                        'fa-IR' => 'فارسی',
                        'en-US' => 'انگلیسی',
                    ]"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

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

            <x-admin.field label="ایمیل سازمان" class="col-span-4">
                <x-admin.form.input
                    name="organization_email"
                    class="direction-ltr"
                    :value="old('organization_email', get_setting('seo', 'organization_email'))"
                />
            </x-admin.field>

            <x-admin.field label="تلفن سازمان" class="col-span-4">
                <x-admin.form.input
                    name="organization_phone"
                    class="direction-ltr"
                    :value="old('organization_phone', get_setting('seo', 'organization_phone'))"
                />
            </x-admin.field>

            <x-admin.field label="تاریخ تاسیس سازمان" class="col-span-4">
                <x-admin.form.input
                    type="date"
                    name="organization_founding_date"
                    :value="old('organization_founding_date', get_setting('seo', 'organization_founding_date'))"
                />
            </x-admin.field>

            <x-admin.field label="نام قانونی سازمان" class="col-span-6">
                <x-admin.form.input
                    name="organization_legal_name"
                    :value="old('organization_legal_name', get_setting('seo', 'organization_legal_name'))"
                />
            </x-admin.field>

            <x-admin.field label="تعداد کارکنان سازمان" class="col-span-6">
                <x-admin.form.select
                    name="organization_number_of_employees"
                    :value="old('organization_number_of_employees', get_setting('seo', 'organization_number_of_employees'))"
                    :options="[
                        '1-10' => '1 تا 10 کارمند',
                        '11-50' => '11 تا 50 کارمند',
                        '51-200' => '51 تا 200 کارمند',
                        '201-500' => '201 تا 500 کارمند',
                        '501-1000' => '501 تا 1000 کارمند',
                        '1001-5000' => '1001 تا 5000 کارمند',
                        '5001-10000' => '5001 تا 10000 کارمند',
                    ]"
                />
            </x-admin.field>

            <x-admin.field label="لینک‌های شبکه‌های اجتماعی و مراجع رسمی" class="col-span-8">
                <x-admin.form.textarea
                    name="organization_same_as"
                    rows="6"
                    placeholder="در هر خط یک آدرس وارد کنید"
                    class="direction-ltr"
                    :value="old('organization_same_as', get_setting('seo', 'organization_same_as'))"
                />
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
