@extends('admin.layout')

@section('title', 'تنظیمات صفحه اصلی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.home.store') }}" method="put" class="js-confirm">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'slider' => 'اسلایدر',
                    'download-products' => 'محصولات دانلودی',
                    'special-posts' => 'نوشته های ویژه',
                    'subscription' => 'اشتراک ویژه',
                    'products' => 'محصولات فیزیکی',
                    'videos' => 'ویدیوهای آموزشی',
                    'testimonials' => 'تجربه کاربران',
                    'posts' => 'مطالب',
                    'shortcuts' => 'میانبرها',
                    'projects' => 'پروژه‌ها',
                ]"
                active="slider"
            >
                <x-admin.tabs.panel name="slider">
                    <div class="space-y-6">
                        <x-admin.metabox title="اسلایدر صفحه اصلی" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    آیتم‌های اسلایدر اصلی صفحه را از این بخش مدیریت کنید.
                                </div>

                                @include('admin.system.settings.partials.home-slider')
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="download-products">
                    <div class="space-y-6">
                        <x-admin.metabox title="محصولات دانلودی" :collapsible="false">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 space-y-4">
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
                                        'category' => '',
                                    ]"
                            >
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <x-admin.field
                                        label="عنوان"
                                    >
                                        <x-admin.form.input
                                            x-bind:name="field(i, 'title')"
                                            x-model="it.title"
                                        />
                                    </x-admin.field>

                                    <x-admin.field
                                        label="تصویر"
                                    >
                                        <x-admin.form.media-picker
                                            mode="repeater"
                                            name="image"
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
                                        label="فعال سازی درصد پیشرفت"
                                    >
                                        <x-admin.form.switch
                                            x-bind:name="field(i, 'enable_progress_percent')"
                                            x-model="it.enable_progress_percent"
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
                                        label="دسته بندی"
                                    >
                                        <x-admin.form.async-select
                                            x-bind:name="`blocks[${index}][category]`"
                                            :multiple="false"
                                            :selected-options="[]"
                                            :search-url="route('admin.categories.ajax-search')"
                                            placeholder="دسته بندی را انتخاب کنید..."
                                        />
                                    </x-admin.field>
                                </div>
                            </x-admin.form.repeater>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="special-posts">
                    <div class="space-y-6">
                        <x-admin.metabox title="نوشته های ویژه" :collapsible="false">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 space-y-4">
                                <x-admin.field label="عنوان باکس"
                                >
                                    <x-admin.form.input
                                        name="special_posts_title"
                                        :value="old('special_posts_title', get_setting('home', 'special_posts_title'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="تعداد نوشته نمایشی"
                                >
                                    <x-admin.form.input
                                        dir="ltr"
                                        min="0"
                                        type="number"
                                        name="special_posts_posts_counts"
                                        :value="old('special_posts_posts_counts', get_setting('home', 'special_posts_posts_counts'))"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="subscription">
                    @include('admin.system.settings.partials.home-subscription')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="products">
                    @include('admin.system.settings.partials.home-products')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="videos">
                    @include('admin.system.settings.partials.home-videos')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="testimonials">
                    @include('admin.system.settings.partials.home-testimonials')
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
