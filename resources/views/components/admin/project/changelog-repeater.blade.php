<x-admin.form.repeater
    :items="$items"
    name="changelogs"
    title="آپدیت‌ها و تغییرات"
    addText="افزودن مورد"
    emptyText="هنوز تغییری ثبت نشده."
    :defaultItem="[
        'title' => '',
        'body' => '',
        'published_at' => '',
    ]"
>
    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 lg:col-span-8">
            <x-admin.field label="عنوان" required>
                <x-admin.form.input x-model="it.title" x-bind:name="field('title')"/>
            </x-admin.field>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <x-admin.field label="published_at">
                <x-admin.form.input type="datetime-local"
                                    x-model="it.published_at"
                                    x-bind:name="field('published_at')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12">
            <x-admin.field label="body">
                <x-admin.form.textarea rows="3"
                                       x-model="it.body"
                                       x-bind:name="field('body')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
