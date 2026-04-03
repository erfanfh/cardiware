<x-admin.form.repeater
    :items="$items"
    name="features"
    title="ویژگی‌های پروژه"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'subtitle' => '',
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
            <x-admin.field label="زیرعنوان">
                <x-admin.form.input
                    x-model="it.subtitle"
                    x-bind:name="field(i, 'subtitle')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
