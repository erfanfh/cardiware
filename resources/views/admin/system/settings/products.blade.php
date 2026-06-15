@extends('admin.layout')

@section('title', 'تنظیمات صفحه محصولات')

@section('content')
    <x-admin.form action="{{ route('admin.settings.products.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="باکس ابتدایی">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان"
                        >
                            <x-admin.form.input
                                name="first_box_title"
                                value="{{ get_setting('products', 'first_box_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="زیرعنوان"
                        >
                            <x-admin.form.input
                                name="first_box_subtitle"
                                value="{{ get_setting('products', 'first_box_subtitle') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="لیبل"
                        >
                            <x-admin.form.input
                                name="first_box_label"
                                value="{{ get_setting('products', 'first_box_label') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="سایر تنظیمات">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="تعداد محصول در هر صفحه"
                        >
                            <x-admin.form.input
                                name="products_per_page"
                                value="{{ get_setting('products', 'products_per_page') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اسلایدر" :collapsible="false">
                    <div class="space-y-4">
                        <div class="text-xs text-slate-500">
                            آیتم‌های اسلایدر را از این بخش مدیریت کنید.
                        </div>

                        <x-admin.form.repeater
                            name="hero_slider"
                            :items="old('hero_slider', get_setting('products', 'hero_slider') ?? [])"
                            title="اسلایدها"
                            addText="افزودن اسلاید"
                            emptyText="هنوز اسلایدی اضافه نشده است."
                            headingKey="title"
                            headingPlaceholder="اسلاید بدون عنوان"
                            :defaultItem="[
                                'title' => '',
                                'url' => '',
                                'image_id' => null,
                                'image_path' => null,
                                'sort_order' => 0,
                            ]"
                        >
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-4">
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
                                        label="لینک اسلاید"
                                        class="col-span-2"
                                    >
                                        <x-admin.form.input
                                            x-bind:name="field(i, 'url')"
                                            x-model="it.url"
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
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
