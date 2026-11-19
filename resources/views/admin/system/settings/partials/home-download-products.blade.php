<div class="space-y-6">
    <x-admin.metabox title="محصولات دانلودی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 space-y-4">
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
                    placeholder="https://moddingway.ir/products"
                />
            </x-admin.field>

            <x-admin.field label="حالت اسلایدر" hint="وقتی فعال باشد، محصولات به‌صورت اسلایدر (ردیف متحرک با دکمه‌های قبلی/بعدی و صفحه‌بند) نمایش داده می‌شوند؛ غیرفعال = شبکهٔ گرید."
            >
                <x-admin.form.switch
                    name="download_products_slider"
                    :checked="old('download_products_slider', get_setting('home', 'download_products_slider'))"
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
                'link' => '',
                'image_id' => null,
                'image_path' => null,
                'enable_progress_percent' => '',
                'progress_percent' => '',
                'label' => '',
                'badge_text' => '',
                'price_text' => '',
                'old_price_text' => '',
                'button_text' => '',
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

                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.field label="بج تصویر (مثلا ۲۰٪)">
                            <x-admin.form.input
                                x-bind:name="field(i, 'badge_text')"
                                x-model="it.badge_text"
                            />
                        </x-admin.field>

                        <x-admin.field label="برچسب (مثلا FC 26)">
                            <x-admin.form.input
                                x-bind:name="field(i, 'label')"
                                x-model="it.label"
                            />
                        </x-admin.field>

                        <x-admin.field label="قیمت">
                            <x-admin.form.input
                                x-bind:name="field(i, 'price_text')"
                                x-model="it.price_text"
                            />
                        </x-admin.field>

                        <x-admin.field label="قیمت قبلی">
                            <x-admin.form.input
                                x-bind:name="field(i, 'old_price_text')"
                                x-model="it.old_price_text"
                            />
                        </x-admin.field>

                        <x-admin.field label="متن دکمه (پیش‌فرض: خرید)" class="col-span-2">
                            <x-admin.form.input
                                x-bind:name="field(i, 'button_text')"
                                x-model="it.button_text"
                            />
                        </x-admin.field>
                    </div>

                    <x-admin.field
                        label="لینک"
                    >
                        <x-admin.form.input
                            x-bind:name="field(i, 'link')"
                            x-model="it.link"
                            class="direction-ltr"
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
