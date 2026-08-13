@php
    $videoGalleryItems = old('video_gallery_items', $product?->getVideoGalleryForForm() ?? []);
@endphp

<x-admin.form.repeater
    name="video_gallery_items"
    :items="$videoGalleryItems"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'video_url' => '',
        'cover' => [
            'id' => null,
            'path' => null,
        ],
    ]"
    addText="افزودن ویدیو"
    emptyText="هنوز ویدیویی برای گالری این محصول اضافه نشده است."
    headingKey="title"
    headingPlaceholder="ویدیوی جدید"
>
    <div class="space-y-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-admin.field label="عنوان ویدیو">
                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </x-admin.field>

            <x-admin.field label="لینک ویدیو">
                <x-admin.form.input
                    type="url"
                    dir="ltr"
                    x-bind:name="field(i, 'video_url')"
                    x-model="it.video_url"
                    placeholder="https://..."
                />
            </x-admin.field>
        </div>

        <x-admin.field label="کاور ویدیو">
            <x-admin.form.media-picker
                mode="repeater"
                name="cover"
                layout="vertical"
                previewHeight="h-48"
            />
        </x-admin.field>
    </div>
</x-admin.form.repeater>
