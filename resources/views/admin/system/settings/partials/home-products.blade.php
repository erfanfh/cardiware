<div class="space-y-6">
    <x-admin.metabox title="محصولات فیزیکی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 space-y-4">
            <x-admin.field label="عنوان باکس">
                <x-admin.form.input
                    name="products_title"
                    :value="old('products_title', get_setting('home', 'products_title'))"
                />
            </x-admin.field>

            <x-admin.field label="لینک همه محصولات">
                <x-admin.form.input
                    name="products_all_products_url" class="direction-ltr"
                    :value="old('products_all_products_url', get_setting('home', 'products_all_products_url'))"
                />
            </x-admin.field>
        </div>

        <x-admin.field>
            <x-admin.form.repeater
                :items="get_setting('home', 'products_items')"
                name="products_items"
                title="آیتم ها"
                addText="افزودن آیتم"
                :defaultItem="[
                    'title' => '',
                    'image_id' => null,
                    'image_path' => null,
                    'button_title' => '',
                    'label' => '',
                    'url' => '',
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-4">
                        <x-admin.field label="عنوان">
                            <x-admin.form.input x-model="it.title"
                                                x-bind:name="field(i, 'title')"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه">
                            <x-admin.form.input x-model="it.button_title"
                                                x-bind:name="field(i, 'button_title')"/>
                        </x-admin.field>

                        <x-admin.field label="لیبل">
                            <x-admin.form.input x-model="it.label"
                                                x-bind:name="field(i, 'label')"/>
                        </x-admin.field>

                        <x-admin.field label="لینک">
                            <x-admin.form.input x-model="it.url" class="direction-ltr"
                                                x-bind:name="field(i, 'url')"/>
                        </x-admin.field>
                    </div>

                    <x-admin.field label="تصویر">
                        <x-admin.form.media-picker
                            mode="repeater"
                            name="image"
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
