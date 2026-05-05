<div class="space-y-6">
    <x-admin.metabox title="باکس خدمات و آپشن‌ها" :collapsible="false">
        <x-admin.form.repeater
            name="services_options_items"
            :items="old('services_options_items', get_setting('home', 'services_options_items') ?? [])"
            title="آیتم ها"
            addText="افزودن آیتم"
            emptyText="هنوز آیتمی اضافه نشده است."
            headingKey="title"
            headingPlaceholder="آیتم بدون عنوان"
            :defaultItem="[
                'title' => '',
                'subtitle' => '',
                'link' => '',
                'icon' => '',
                'button_text' => 'مشاهده',
                'theme' => 'dark',
            ]"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <x-admin.field label="عنوان">
                    <x-admin.form.input
                        x-bind:name="field(i, 'title')"
                        x-model="it.title"
                    />
                </x-admin.field>

                <x-admin.field label="زیرعنوان">
                    <x-admin.form.input
                        x-bind:name="field(i, 'subtitle')"
                        x-model="it.subtitle"
                    />
                </x-admin.field>

                <x-admin.field label="لینک">
                    <x-admin.form.input
                        dir="ltr"
                        class="direction-ltr"
                        x-bind:name="field(i, 'link')"
                        x-model="it.link"
                    />
                </x-admin.field>

                <x-admin.field label="آیکون / ایموجی">
                    <x-admin.form.input
                        x-bind:name="field(i, 'icon')"
                        x-model="it.icon"
                    />
                </x-admin.field>

                <x-admin.field label="متن دکمه">
                    <x-admin.form.input
                        x-bind:name="field(i, 'button_text')"
                        x-model="it.button_text"
                    />
                </x-admin.field>

                <x-admin.field label="حالت نمایش">
                    <x-admin.form.select
                        x-bind:name="field(i, 'theme')"
                        x-model="it.theme"
                        :options="[
                            ['value' => 'dark', 'label' => 'تیره'],
                            ['value' => 'light', 'label' => 'روشن'],
                        ]"
                    />
                </x-admin.field>
            </div>
        </x-admin.form.repeater>
    </x-admin.metabox>
</div>
