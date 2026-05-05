<div class="space-y-6">
    <x-admin.metabox title="اسلایدر صفحه اصلی" :collapsible="false">
        <div class="space-y-4">
            <div class="text-xs text-slate-500">
                آیتم‌های اسلایدر اصلی صفحه را از این بخش مدیریت کنید.
            </div>

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
                    'url' => '',
                    'top_label_text' => '',
                    'square_label_text' => '',
                    'description' => '',
                    'primary_button_text' => '',
                    'primary_button_url' => '',
                    'secondary_button_text' => '',
                    'secondary_button_url' => '',
                    'image_id' => null,
                    'image_path' => null,
                    'show_dark_overlay' => true,
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                    <x-admin.field
                        label="عنوان اصلی"
                        class="col-span-2"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'title')"
                            x-model="it.title"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="عنوان فرعی"
                        class="col-span-2"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'subtitle')"
                            x-model="it.subtitle"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="لینک اسلاید"
                        class="col-span-2"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'url')"
                            x-model="it.url"
                            class="direction-ltr"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="نمایش هاله تیره"
                        class="col-span-2 switch-field"
                    >
                        <x-admin.form.switch
                            x-bind:name="field(i, 'show_dark_overlay')"
                            x-model="it.show_dark_overlay"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن لیبل بالا"
                        class="col-span-2"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'top_label_text')"
                            x-model="it.top_label_text"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن لیبل مربعی"
                        class="col-span-2"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'square_label_text')"
                            x-model="it.square_label_text"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="تصویر"
                        class="lg:col-span-6"
                    >
                        <x-admin.form.media-picker
                            mode="repeater"
                            name="image"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="توضیحات"
                        class="lg:col-span-6"
                    >
                        <x-admin.form.textarea
                            rows="4"
                            x-bind:name="field(i, 'description')"
                            x-model="it.description"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن دکمه اصلی"
                        class="col-span-3"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'primary_button_text')"
                            x-model="it.primary_button_text"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="لینک دکمه اصلی"
                        class="col-span-3"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'primary_button_url')"
                            x-model="it.primary_button_url"
                            class="direction-ltr"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن دکمه دوم"
                        class="col-span-3"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'secondary_button_text')"
                            x-model="it.secondary_button_text"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="لینک دکمه دوم"
                        class="col-span-3"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'secondary_button_url')"
                            x-model="it.secondary_button_url"
                            class="direction-ltr"
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </div>
    </x-admin.metabox>
</div>
