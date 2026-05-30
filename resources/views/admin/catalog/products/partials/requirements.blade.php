@php
    $items = old('requirements', $product?->requirements ?? []);
@endphp

<x-admin.form.repeater
    name="requirements"
    :items="$items"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'value' => '',
    ]"
    addText="افزودن آیتم"
    emptyText="هنوز آیتمی اضافه نشده است."
    headingKey="title"
    headingPlaceholder="آیتم جدید"
>
    <div class="space-y-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </x-admin.field>

            <x-admin.field label="مقدار" class="col-span-3">
                <x-admin.form.input
                    x-bind:name="field(i, 'value')"
                    x-model="it.value"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
