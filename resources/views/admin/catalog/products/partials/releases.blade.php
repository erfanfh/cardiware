@php
    $items = old('releases');

    if ($items === null && isset($product)) {
        $items = $product->releases()
            ->with(['releaseChanges' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($release) => [
                'id' => $release->id,
                'title' => $release->title,
                'version' => $release->version,
                'excerpt' => $release->excerpt,
                'released_at' => $release->released_at ? jalali_date($release->released_at) : '',
                'changes_body' => $release->changes_body,
            ])
            ->values()
            ->toArray();
    }

    $items ??= [];
@endphp

<x-admin.form.repeater
    name="releases"
    :items="$items"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'version' => '',
        'excerpt' => '',
        'released_at' => '',
        'changes_body' => '',
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

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-bind:name="field(i, 'title')"
                    x-model="it.title"
                />
            </x-admin.field>

            <x-admin.field label="تاریخ">
                <x-admin.form.date-picker
                    x-model="it.released_at"
                    x-bind:name="field(i, 'released_at')"
                />
            </x-admin.field>

            <x-admin.field label="توضیح" class="col-span-2">
                <x-admin.form.input
                    x-bind:name="field(i, 'excerpt')"
                    x-model="it.excerpt"
                />
            </x-admin.field>

            <x-admin.field label="تغییرات" class="col-span-2">
                <div x-data="repeaterEditor()">
                    <x-admin.form.editor
                        x-bind:name="field(i, 'changes_body')"
                        x-model="it.changes_body"
                        preset="full"
                        height="400"
                        :toolbar-sticky="false"
                    />
                </div>
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
