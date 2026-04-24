<div class="space-y-6">
    <x-admin.metabox title="نوشته های ویژه" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4 mb-4">
            <x-admin.field label="عنوان باکس"
            >
                <x-admin.form.input
                    name="special_posts_title"
                    :value="old('special_posts_title', get_setting('home', 'special_posts_title'))"
                />
            </x-admin.field>
        </div>

        <x-admin.form.repeater
            name="special_posts_items"
            :items="old('special_posts_items', get_setting('home', 'special_posts_items') ?? [])"
            title="آیتم ها"
            addText="افزودن آیتم"
            emptyText="هنوز آیتمی اضافه نشده است."
            headingKey="title"
            headingPlaceholder="آیتم بدون عنوان"
            :defaultItem="[
                'title' => '',
                'short_description' => '',
                'link' => '',
                'image_id' => null,
                'image_path' => null,
                'label' => '',
            ]"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="flex flex-col gap-4">
                    <x-admin.field
                        label="عنوان"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'title')"
                            x-model="it.title"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="توضیح کوتاه"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'short_description')"
                            x-model="it.short_description"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="لینک"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'link')"
                            x-model="it.link"
                            class="direction-ltr"
                        />
                    </x-admin.field>

                </div>

                <x-admin.field
                    label="تصویر"
                >
                    <x-admin.form.media-picker
                        mode="repeater"
                        name="image"
                    />
                </x-admin.field>
            </div>
        </x-admin.form.repeater>
    </x-admin.metabox>
</div>
