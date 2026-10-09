@php
    $requirementsText = old('requirements_text', implode(PHP_EOL, $position?->requirements ?? []));
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field class="col-span-12" label="تاریخ انتشار">
                    <x-admin.form.date-picker name="published_at" :value="old('published_at', jalali_date($position?->published_at))"/>
                </x-admin.field>
                <x-admin.field class="col-span-12" label="وضعیت">
                    <x-admin.form.select name="status" :options="['draft' => 'پیش‌نویس', 'active' => 'فعال', 'closed' => 'بسته']" :value="old('status', $position?->status ?? 'draft')"/>
                </x-admin.field>
            </div>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$position?->title ?? ''"
        :slugValue="$position?->slug ?? ''"
        :basePath="url('/careers') . '/'"
        :checkUrl="route('admin.career-positions.check-slug')"
        :currentId="$position?->id"
    />

    <x-admin.metabox title="اطلاعات موقعیت">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
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
                <x-admin.form.textarea name="requirements_text" rows="8" :value="$requirementsText"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>
</x-admin.page>
