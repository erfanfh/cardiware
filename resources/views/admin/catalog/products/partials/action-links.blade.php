@php
    $items = old('action_links');

    if ($items === null && isset($product) && $product) {
        $items = $product->actionLinks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'open_in_new_tab' => $link->open_in_new_tab ? '1' : '0',
            ])
            ->values()
            ->toArray();
    }

    $items ??= [];
@endphp

<x-admin.form.repeater
    name="action_links"
    :items="$items"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'url' => '',
        'open_in_new_tab' => '1',
    ]"
    addText="افزودن دکمه"
    emptyText="هنوز دکمه تکمیلی برای این محصول اضافه نشده است."
    headingKey="title"
    headingPlaceholder="دکمه جدید"
>
    <div class="grid lg:grid-cols-3 gap-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <x-admin.field label="عنوان دکمه">
            <x-admin.form.input
                x-bind:name="field(i, 'title')"
                x-model="it.title"
            />
        </x-admin.field>

        <x-admin.field label="آدرس لینک">
            <x-admin.form.input
                type="url"
                dir="ltr"
                x-bind:name="field(i, 'url')"
                x-model="it.url"
            />
        </x-admin.field>

        <x-admin.field
            label="نحوه باز شدن لینک"
        >
            <x-admin.form.select
                x-bind:name="field(i, 'open_in_new_tab')"
                x-model="it.open_in_new_tab"
                :options="[
                    '1' => 'باز شدن در تب جدید',
                    '0' => 'باز شدن در همین صفحه',
                ]"
            />
        </x-admin.field>
    </div>
</x-admin.form.repeater>
