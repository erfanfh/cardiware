<x-admin.form.repeater
    :items="$items"
    name="changelogs"
    title="آپدیت‌ها و تغییرات"
    addText="افزودن مورد"
    emptyText="هنوز تغییری ثبت نشده."
    :defaultItem="[
        'id' => null,
        'title' => '',
        'tags' => '',
        'body' => '',
        'published_at' => '',
    ]"
>
    <input type="hidden" x-bind:name="field(i, 'id')" x-model="it.id">

    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 lg:col-span-8">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-model="it.title"
                    x-bind:name="field(i, 'title')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <x-admin.field label="تاریخ انتشار">
                <x-admin.form.date-picker
                    x-model="it.published_at"
                    x-bind:name="field(i, 'published_at')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12">
            <x-admin.field label="برچسب ها (با کاما جدا کنید)">
                <x-admin.form.input
                    x-model="it.tags"
                    x-bind:name="field(i, 'tags')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12">
            <x-admin.field label="توضیحات">
                <x-admin.form.textarea
                    rows="3"
                    x-model="it.body"
                    x-bind:name="field(i, 'body')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
