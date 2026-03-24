<x-admin.form.repeater
    name="hero_slider"
    :items="old('hero_slider', get_setting('home', 'hero_slider') ?? [])"
    title="اسلایدهای هیرو"
    addText="افزودن اسلاید"
    emptyText="هنوز اسلایدی اضافه نشده است."
    headingKey="title"
    headingPlaceholder="اسلاید بدون عنوان"
    :defaultItem="[
        'title' => '',
        'subtitle' => '',
        'top_label_text' => '',
        'square_label_text' => '',
        'description' => '',
        'primary_button_text' => '',
        'primary_button_url' => '',
        'secondary_button_text' => '',
        'secondary_button_url' => '',
        'image_id' => null,
        'image_path' => null,
        'sort_order' => 0,
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-admin.field
            label="عنوان اصلی"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'title')"
                x-model="it.title"
            />
        </x-admin.field>

        <x-admin.field
            label="عنوان فرعی"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'subtitle')"
                x-model="it.subtitle"
            />
        </x-admin.field>

        <x-admin.field
            label="متن لیبل بالا"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'top_label_text')"
                x-model="it.top_label_text"
            />
        </x-admin.field>

        <x-admin.field
            label="متن لیبل مربعی"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'square_label_text')"
                x-model="it.square_label_text"
            />
        </x-admin.field>

        <x-admin.field
            label="تصویر"
            class="lg:col-span-2"
        >
            <x-admin.form.media-picker
                mode="repeater"
                name="image"
            />
        </x-admin.field>

        <x-admin.field
            label="توضیحات"
            class="lg:col-span-2"
        >
            <x-admin.form.textarea
                rows="4"
                x-bind:name="field(i, 'description')"
                x-model="it.description"
            />
        </x-admin.field>

        <x-admin.field
            label="متن دکمه اصلی"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'primary_button_text')"
                x-model="it.primary_button_text"
            />
        </x-admin.field>

        <x-admin.field
            label="لینک دکمه اصلی"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'primary_button_url')"
                x-model="it.primary_button_url"
                class="direction-ltr"
            />
        </x-admin.field>

        <x-admin.field
            label="متن دکمه دوم"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'secondary_button_text')"
                x-model="it.secondary_button_text"
            />
        </x-admin.field>

        <x-admin.field
            label="لینک دکمه دوم"
        >
            <x-admin.form.input
                x-bind:name="field(i, 'secondary_button_url')"
                x-model="it.secondary_button_url"
                class="direction-ltr"
            />
        </x-admin.field>
    </div>
</x-admin.form.repeater>
