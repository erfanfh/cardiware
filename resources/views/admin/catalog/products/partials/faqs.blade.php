@php
    $items = old('faqs', $product?->faqs ?? []);
@endphp

<x-admin.form.repeater
    name="faqs"
    :items="$items"
    :defaultItem="[
        'id' => null,
        'question' => '',
        'answer' => '',
    ]"
    addText="افزودن سوال"
    emptyText="هنوز سوالی اضافه نشده است."
    headingKey="question"
    headingPlaceholder="سوال جدید"
>
    <div class="space-y-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <div class="grid grid-cols-1 gap-4">
            <x-admin.field label="سوال">
                <x-admin.form.input
                    x-bind:name="field(i, 'question')"
                    x-model="it.question"
                />
            </x-admin.field>

            <x-admin.field label="پاسخ">
                <x-admin.form.textarea
                    rows="4"
                    x-bind:name="field(i, 'answer')"
                    x-model="it.answer"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
