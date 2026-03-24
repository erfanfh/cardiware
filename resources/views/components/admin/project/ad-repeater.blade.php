<x-admin.form.repeater
    :items="$items"
    name="ads"
    title="تبلیغات پروژه"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'link' => '',
    ]"
>
    <input type="hidden" x-bind:name="field(i, 'id')" x-model="it.id">

    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 lg:col-span-6">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-model="it.title"
                    x-bind:name="field(i, 'title')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 lg:col-span-6">
            <x-admin.field label="لینک">
                <x-admin.form.input
                    x-model="it.link"
                    x-bind:name="field(i, 'link')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
