@php
    $items = old('installation_steps', $product?->installationSteps ?? []);
@endphp

<x-admin.form.repeater
    name="installation_steps"
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

        <div class="grid grid-cols-1 gap-4">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </x-admin.field>

            <x-admin.field label="توضیحات">
                <x-admin.form.textarea
                    rows="4"
                    x-bind:name="field(i, 'value')"
                    x-model="it.value"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
