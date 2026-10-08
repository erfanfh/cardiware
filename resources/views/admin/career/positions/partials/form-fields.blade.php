<x-admin.form.validation-errors class="mb-6"/>
<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار" :collapsible="false">
            <div class="space-y-4">
                <x-admin.field label="وضعیت"><x-admin.form.select name="status" :options="['draft' => 'پیش‌نویس', 'active' => 'فعال', 'closed' => 'بسته']" :value="old('status', $position?->status ?? 'draft')"/></x-admin.field>
                <x-admin.field label="ترتیب نمایش"><x-admin.form.input name="sort_order" type="number" min="0" :value="old('sort_order', $position?->sort_order ?? 0)"/></x-admin.field>
                <x-admin.field label="شروع نمایش" hint="خالی بماند: بلافاصله پس از فعال‌سازی"><x-admin.form.input name="published_at" type="datetime-local" :value="old('published_at', $position?->published_at?->format('Y-m-d\TH:i'))"/></x-admin.field>
                <x-admin.field label="پایان نمایش" hint="خالی بماند: بدون تاریخ پایان"><x-admin.form.input name="closes_at" type="datetime-local" :value="old('closes_at', $position?->closes_at?->format('Y-m-d\TH:i'))"/></x-admin.field>
            </div>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.metabox title="اطلاعات موقعیت">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-admin.field label="عنوان"><x-admin.form.input name="title" :value="old('title', $position?->title)"/></x-admin.field>
            <x-admin.field label="نامک" hint="اختیاری؛ برای لینک آینده صفحه"><x-admin.form.input name="slug" dir="ltr" :value="old('slug', $position?->slug)"/></x-admin.field>
            <x-admin.field label="تیم / حوزه"><x-admin.form.input name="department" :value="old('department', $position?->department)"/></x-admin.field>
            <x-admin.field label="برچسب نوع استخدام" hint="مثلاً تمام‌وقت / پاره‌وقت"><x-admin.form.input name="employment_type" :value="old('employment_type', $position?->employment_type)"/></x-admin.field>
            <x-admin.field label="نوع همکاری در باکس جزئیات" hint="مثلاً منعطف یا پروژه‌ای"><x-admin.form.input name="cooperation_type" :value="old('cooperation_type', $position?->cooperation_type)"/></x-admin.field>
            <x-admin.field label="مکان"><x-admin.form.input name="location" :value="old('location', $position?->location)"/></x-admin.field>
            <x-admin.field label="سطح"><x-admin.form.input name="level" :value="old('level', $position?->level)"/></x-admin.field>
            <x-admin.field label="عنوان ویژگی تکمیلی"><x-admin.form.input name="detail_label" :value="old('detail_label', $position?->detail_label)"/></x-admin.field>
            <x-admin.field label="مقدار ویژگی تکمیلی"><x-admin.form.input name="detail_value" :value="old('detail_value', $position?->detail_value)"/></x-admin.field>
        </div>
    </x-admin.metabox>
    <x-admin.metabox title="شرح و نیازمندی‌ها">
        <div class="space-y-5">
            <x-admin.field label="خلاصه موقعیت"><x-admin.form.textarea name="summary" rows="5" :value="old('summary', $position?->summary)"/></x-admin.field>
            <x-admin.field label="نیازمندی‌ها" hint="هر مورد را در یک خط بنویسید.">
                <x-admin.form.textarea name="requirements_text" rows="8" :value="old('requirements_text', implode("\n", $position?->requirements ?? []))"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>
</x-admin.page>
