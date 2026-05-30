@php
    $imageGalleryItems = old('image_gallery_items', $product?->getImageGalleryForForm() ?? []);
@endphp

<x-admin.form.repeater
    name="image_gallery_items"
    :items="$imageGalleryItems"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'image' => '',
    ]"
    addText="افزودن تصویر"
    emptyText="هنوز تصویری برای گالری اضافه نشده است."
    headingKey="title"
    headingPlaceholder="تصویر جدید"
>
    <div class="space-y-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </x-admin.field>

            <x-admin.field label="تصویر">
                <x-admin.form.media-picker
                    mode="repeater"
                    name="image"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
