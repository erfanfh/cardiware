<div class="space-y-6">
    <x-admin.metabox title="محصولات دانلودی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان باکس"
            >
                <x-admin.form.input
                    name="download_products_title"
                    :value="old('download_products_title', get_setting('home', 'download_products_title'))"
                />
            </x-admin.field>

            <x-admin.field label="لینک همه محصولات"
            >
                <x-admin.form.input
                    dir="ltr"
                    name="download_products_all_url"
                    :value="old('download_products_all_url', get_setting('home', 'download_products_all_url'))"
                />
            </x-admin.field>
        </div>

        <x-admin.form.repeater
            name="download_products_items"
            :items="old('download_products_items', get_setting('home', 'download_products_items') ?? [])"
            title="آیتم ها"
            addText="افزودن آیتم"
            emptyText="هنوز آیتمی اضافه نشده است."
            headingKey="title"
            headingPlaceholder="آیتم بدون عنوان"
            :defaultItem="[
                'title' => '',
                'short_description' => '',
                'image_id' => null,
                'image_path' => null,
                'enable_progress_percent' => '',
                'progress_percent' => '',
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
                        label="لیبل"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'label')"
                            x-model="it.label"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="درصد پیشرفت"
                    >
                        <x-admin.form.input
                            type="number"
                            min="0"
                            max="100"
                            dir="ltr"
                            x-bind:name="field(i, 'progress_percent')"
                            x-model="it.progress_percent"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="فعال سازی درصد پیشرفت"
                        class="switch-field"
                    >
                        <x-admin.form.switch
                            x-bind:name="field(i, 'enable_progress_percent')"
                            x-model="it.enable_progress_percent"
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
