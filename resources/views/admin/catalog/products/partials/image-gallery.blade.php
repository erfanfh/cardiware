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
    title="گالری تصاویر"
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
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700">
                    عنوان
                </label>

                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700">
                    تصویر
                </label>

                <x-admin.form.media-picker
                    mode="repeater"
                    name="image"
                />
            </div>
        </div>
    </div>
</x-admin.form.repeater>
