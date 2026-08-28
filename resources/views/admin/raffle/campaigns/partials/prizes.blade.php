@php
    $prizes = old('prizes', $drawCampaign?->prizes?->map(fn($prize) => [
        'id' => $prize->id,
        'title' => $prize->title,
        'description' => $prize->description,
        'delivery_description' => $prize->delivery_description,
        'estimated_value' => $prize->estimated_value,
        'quantity' => $prize->quantity,
        'reserve_winners_count' => $prize->reserve_winners_count,
        'media' => ['id' => $prize->media_file_id, 'path' => $prize->mediaFile?->url],
    ])->values()->all() ?? []);
@endphp

<x-admin.metabox title="جوایز">
    <x-admin.form.repeater
        name="prizes"
        :items="$prizes"
        :defaultItem="[
            'id' => null, 'title' => '', 'description' => '', 'delivery_description' => '',
            'estimated_value' => '', 'quantity' => 1, 'reserve_winners_count' => '',
            'media' => ['id' => null, 'path' => null],
        ]"
        addText="افزودن جایزه"
        emptyText="هنوز جایزه ای تعریف نشده است."
        headingKey="title"
        headingPlaceholder="جایزه جدید"
    >
        <div class="space-y-4">
            <input type="hidden" x-bind:name="field(i, 'id')" x-model="it.id">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field required label="عنوان جایزه"><x-admin.form.input x-bind:name="field(i, 'title')" x-model="it.title"/></x-admin.field>
                <x-admin.field label="ارزش تقریبی (تومان)"><x-admin.form.input type="number" min="0" dir="ltr" x-bind:name="field(i, 'estimated_value')" x-model="it.estimated_value"/></x-admin.field>
                <x-admin.field required label="تعداد"><x-admin.form.input type="number" min="1" dir="ltr" x-bind:name="field(i, 'quantity')" x-model="it.quantity"/></x-admin.field>
                <x-admin.field label="تعداد برنده ذخیره" hint="خالی باشد از مقدار پیش فرض کمپین استفاده می‌شود."><x-admin.form.input type="number" min="0" dir="ltr" x-bind:name="field(i, 'reserve_winners_count')" x-model="it.reserve_winners_count"/></x-admin.field>
                <div class="md:col-span-2"><x-admin.field label="توضیحات"><x-admin.form.textarea rows="3" x-bind:name="field(i, 'description')" x-model="it.description"/></x-admin.field></div>
                <div class="md:col-span-2"><x-admin.field label="توضیحات تحویل جایزه"><x-admin.form.textarea rows="2" x-bind:name="field(i, 'delivery_description')" x-model="it.delivery_description"/></x-admin.field></div>
                <div class="md:col-span-2"><x-admin.field label="تصویر جایزه"><x-admin.form.media-picker mode="repeater" name="media" layout="vertical" previewHeight="h-44"/></x-admin.field></div>
            </div>
        </div>
    </x-admin.form.repeater>
</x-admin.metabox>
