@extends('admin.layout')

@section('title', 'تنظیمات صفحه اصلی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.home.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'slider' => 'اسلایدر',
                    'download-products' => 'محصولات دانلودی',
                    'special-posts' => 'نوشته های ویژه',
                    'subscription' => 'اشتراک ویژه',
                    'products' => 'محصولات فیزیکی',
                    'videos' => 'ویدیوهای آموزشی',
                    'posts' => 'مطالب',
                    'shortcuts' => 'میانبرها',
                    'projects' => 'پروژه‌ها',
                    'testimonials' => 'تجربه کاربران',
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
                    <div class="space-y-6">
                        <x-admin.metabox title="اشتراک ویژه" :collapsible="false">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 space-y-4">
                                <x-admin.field label="عنوان"
                                >
                                    <x-admin.form.input
                                        name="subscription_title"
                                        :value="old('subscription_title', get_setting('home', 'subscription_title'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="زیرعنوان"
                                >
                                    <x-admin.form.input
                                        name="subscription_subtitle"
                                        :value="old('subscription_subtitle', get_setting('home', 'subscription_subtitle'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="متن دکمه"
                                >
                                    <x-admin.form.input
                                        name="subscription_button_title"
                                        :value="old('subscription_button_title', get_setting('home', 'subscription_button_title'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="لینک دکمه"
                                >
                                    <x-admin.form.input
                                        dir="ltr"
                                        name="subscription_button_url"
                                        :value="old('subscription_button_url', get_setting('home', 'subscription_button_url'))"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="products">
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
                                        name="products_all_products_url"
                                        :value="old('products_all_products_url', get_setting('home', 'products_all_products_url'))"
                                    />
                                </x-admin.field>
                            </div>

                            <x-admin.field label="آیتم ها">
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
                                            'category' => '',
                                            'url' => '',
                                            'sort_order' => 0,
                                        ]"
                                >
                                    <div class="grid grid-cols-2 gap-3">
                                        <x-admin.field label="عنوان">
                                            <x-admin.form.input x-model="it.title"
                                                                x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>

                                        <x-admin.field label="متن دکمه">
                                            <x-admin.form.input x-model="it.button_title"
                                                                x-bind:name="field(i, 'button_title')"/>
                                        </x-admin.field>

                                        <x-admin.field label="تصویر">
                                            <x-admin.form.media-picker
                                                mode="repeater"
                                                name="image"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="دسته بندی">
                                            <x-admin.form.async-select
                                                x-bind:name="`blocks[${index}][category]`"
                                                :multiple="false"
                                                :selected-options="[]"
                                                :search-url="route('admin.categories.ajax-search')"
                                                placeholder="دسته بندی را انتخاب کنید..."
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="لینک">
                                            <x-admin.form.input x-model="it.url"
                                                                x-bind:name="field(i, 'url')"/>
                                        </x-admin.field>
                                    </div>
                                </x-admin.form.repeater>
                            </x-admin.field>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="videos">
                    <div class="space-y-6">
                        <x-admin.metabox title="ویدیوهای آموزشی" :collapsible="false">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 space-y-4">
                                <x-admin.field label="عنوان باکس">
                                    <x-admin.form.input
                                        name="videos_title"
                                        :value="old('videos_title', get_setting('home', 'videos_title'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="دسته های مجاز">
                                    <x-admin.form.async-select
                                        name="videos_category_ids"
                                        :multiple="true"
                                        :value="old('videos_category_ids', get_setting('home', 'videos_category_ids')) ?? []"
                                        :selected-options="\App\Domain\Taxonomy\Models\TaxonomyTerm::categories()
                                                                    ->whereIn('id', old('videos_category_ids', get_setting('home', 'videos_category_ids')))
                                                                    ->get()
                                                                    ->map(fn ($c) => [
                                                                        'value' => $c->id,
                                                                        'label' => $c->title,
                                                                    ])
                                                                    ->all() ?? []"
                                        :search-url="route('admin.categories.ajax-search')"
                                        placeholder="دسته‌ها را جستجو کنید..."
                                        :error="$errors->first('videos_category_ids')"
                                    />
                                </x-admin.field>

                                <x-admin.field label="توضیحات باکس">
                                    <x-admin.form.textarea
                                        rows="3"
                                        name="videos_description"
                                        :value="old('videos_description', get_setting('home', 'videos_description'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="متن دکمه باکس">
                                    <x-admin.form.input
                                        name="videos_button_title"
                                        :value="old('videos_button_title', get_setting('home', 'videos_button_title'))"
                                    />
                                </x-admin.field>

                                <x-admin.field label="لینک دکمه باکس">
                                    <x-admin.form.input
                                        dir="ltr"
                                        name="videos_button_url"
                                        :value="old('videos_button_url', get_setting('home', 'videos_button_url'))"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                {{--                <x-admin.tabs.panel name="subscription">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="باکس اشتراک ویژه" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="نمایش باکس اشتراک ویژه"--}}
                {{--                                    hint="فعال یا غیرفعال کردن این بخش در صفحه اصلی."--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.switch--}}
                {{--                                        name="home_subscription_box_enabled"--}}
                {{--                                        :checked="true"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <x-admin.field--}}
                {{--                                    label="عنوان باکس"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_subscription_box_title"--}}
                {{--                                        placeholder="مثلاً اشتراک ویژه مودینگ وی"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <div class="lg:col-span-2">--}}
                {{--                                    <x-admin.field--}}
                {{--                                        label="متن باکس"--}}
                {{--                                    >--}}
                {{--                                        <x-admin.form.textarea--}}
                {{--                                            name="home_subscription_box_text"--}}
                {{--                                            rows="4"--}}
                {{--                                            placeholder="توضیح کوتاهی درباره اشتراک ویژه..."--}}
                {{--                                        />--}}
                {{--                                    </x-admin.field>--}}
                {{--                                </div>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="posts">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="نظرسنجی صفحه اصلی" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="نمایش نظرسنجی در صفحه اصلی"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.switch--}}
                {{--                                        name="home_poll_enabled"--}}
                {{--                                        :checked="true"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}

                {{--                        <x-admin.metabox title="مطالب ویژه" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="دسته‌ها"--}}
                {{--                                    hint="بعداً می‌تواند با select چندگانه جایگزین شود."--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.select--}}
                {{--                                        name="home_featured_posts_category"--}}
                {{--                                        placeholder="انتخاب دسته"--}}
                {{--                                        :options="[--}}
                {{--                                    'news' => 'اخبار',--}}
                {{--                                    'patches' => 'پچ‌ها',--}}
                {{--                                    'mods' => 'مادها',--}}
                {{--                                ]"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <x-admin.field--}}
                {{--                                    label="تعداد مطالب"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        type="number"--}}
                {{--                                        name="home_featured_posts_count"--}}
                {{--                                        placeholder="مثلاً 6"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}

                {{--                        <x-admin.metabox title="تب‌های مطالب" :collapsible="false">--}}
                {{--                            <div class="space-y-4">--}}
                {{--                                <div class="text-xs text-slate-500">--}}
                {{--                                    دسته‌های اسلایدر مطالب و دو باکس لیستی دیگر را از این بخش مدیریت کنید.--}}
                {{--                                </div>--}}

                {{--                                <x-admin.form.repeater--}}
                {{--                                    name="home_post_tabs"--}}
                {{--                                    addLabel="افزودن تب مطلب"--}}
                {{--                                    emptyTitle="هنوز تبی ثبت نشده است"--}}
                {{--                                    emptyDescription="عنوان تب و دسته مرتبط را بعداً در آیتم‌های ریپیتر قرار می‌دهیم."--}}
                {{--                                >--}}
                {{--                                    --}}{{-- shell repeater items --}}
                {{--                                </x-admin.form.repeater>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="shortcuts">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="آیتم‌های میانبر" :collapsible="false">--}}
                {{--                            <div class="space-y-4">--}}
                {{--                                <div class="text-xs text-slate-500">--}}
                {{--                                    آیتم‌های میانبر شامل عنوان اصلی، عنوان فرعی، آیکون و دکمه هستند.--}}
                {{--                                </div>--}}

                {{--                                <x-admin.form.repeater--}}
                {{--                                    name="home_shortcut_items"--}}
                {{--                                    addLabel="افزودن آیتم میانبر"--}}
                {{--                                    emptyTitle="هنوز آیتمی ثبت نشده است"--}}
                {{--                                    emptyDescription="می‌توانید میانبرهای مختلف برای بخش‌های مهم سایت اضافه کنید."--}}
                {{--                                >--}}
                {{--                                    --}}{{-- shell repeater items --}}
                {{--                                </x-admin.form.repeater>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="products">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="محصولات فیزیکی" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="عنوان باکس"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_products_box_title"--}}
                {{--                                        placeholder="مثلاً جدیدترین محصولات فیزیکی"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <x-admin.field--}}
                {{--                                    label="متن دکمه باکس"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_products_box_button_text"--}}
                {{--                                        placeholder="مثلاً مشاهده همه"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <div class="lg:col-span-2">--}}
                {{--                                    <x-admin.field--}}
                {{--                                        label="لینک دکمه باکس"--}}
                {{--                                    >--}}
                {{--                                        <x-admin.form.input--}}
                {{--                                            name="home_products_box_button_url"--}}
                {{--                                            placeholder="https://example.com/products"--}}
                {{--                                        />--}}
                {{--                                    </x-admin.field>--}}
                {{--                                </div>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}

                {{--                        <x-admin.metabox title="آیتم‌های محصولات" :collapsible="false">--}}
                {{--                            <div class="space-y-4">--}}
                {{--                                <div class="text-xs text-slate-500">--}}
                {{--                                    هر آیتم شامل تصویر، عنوان، لینک و لیبل خواهد بود.--}}
                {{--                                </div>--}}

                {{--                                <x-admin.form.repeater--}}
                {{--                                    name="home_product_items"--}}
                {{--                                    addLabel="افزودن محصول"--}}
                {{--                                    emptyTitle="هنوز محصولی ثبت نشده است"--}}
                {{--                                    emptyDescription="آیتم‌های قابل نمایش در باکس محصولات صفحه اصلی را اینجا مدیریت کنید."--}}
                {{--                                >--}}
                {{--                                    --}}{{-- shell repeater items --}}
                {{--                                </x-admin.form.repeater>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="projects">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="پروژه‌ها" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="عنوان باکس پروژه‌ها"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_projects_box_title"--}}
                {{--                                        placeholder="مثلاً پروژه‌های مودینگ وی"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}

                {{--                        <x-admin.metabox title="آیتم‌های پروژه قابل نمایش" :collapsible="false">--}}
                {{--                            <div class="space-y-4">--}}
                {{--                                <div class="text-xs text-slate-500">--}}
                {{--                                    بعداً این بخش می‌تواند به انتخاب پروژه‌های واقعی از دیتابیس متصل شود.--}}
                {{--                                </div>--}}

                {{--                                <x-admin.form.repeater--}}
                {{--                                    name="home_selected_projects"--}}
                {{--                                    addLabel="افزودن پروژه"--}}
                {{--                                    emptyTitle="هنوز پروژه‌ای انتخاب نشده است"--}}
                {{--                                    emptyDescription="فعلاً به صورت نمایشی و ساختاری پیاده‌سازی شده است."--}}
                {{--                                >--}}
                {{--                                    --}}{{-- shell repeater items --}}
                {{--                                </x-admin.form.repeater>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="videos">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="ویدیوهای آموزشی" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="عنوان باکس"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_videos_box_title"--}}
                {{--                                        placeholder="مثلاً ویدیوهای آموزشی"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <x-admin.field--}}
                {{--                                    label="دسته"--}}
                {{--                                    hint="فعلاً تک‌انتخابه است و بعداً می‌تواند چندانتخابه شود."--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.select--}}
                {{--                                        name="home_videos_category"--}}
                {{--                                        placeholder="انتخاب دسته"--}}
                {{--                                        :options="[--}}
                {{--                                    'tutorials' => 'آموزشی',--}}
                {{--                                    'gameplay' => 'گیم‌پلی',--}}
                {{--                                    'guides' => 'راهنماها',--}}
                {{--                                ]"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}

                {{--                <x-admin.tabs.panel name="testimonials">--}}
                {{--                    <div class="space-y-6">--}}
                {{--                        <x-admin.metabox title="تنظیمات کلی تجربه کاربران" :collapsible="false">--}}
                {{--                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">--}}
                {{--                                <x-admin.field--}}
                {{--                                    label="عنوان باکس"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_testimonials_box_title"--}}
                {{--                                        placeholder="مثلاً تجربه کاربران"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}

                {{--                                <x-admin.field--}}
                {{--                                    label="میانگین رضایت کاربران"--}}
                {{--                                >--}}
                {{--                                    <x-admin.form.input--}}
                {{--                                        name="home_testimonials_average_rate"--}}
                {{--                                        placeholder="مثلاً 4.8 از 5"--}}
                {{--                                    />--}}
                {{--                                </x-admin.field>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}

                {{--                        <x-admin.metabox title="آیتم‌های تجربه کاربران" :collapsible="false">--}}
                {{--                            <div class="space-y-4">--}}
                {{--                                <div class="text-xs text-slate-500">--}}
                {{--                                    هر آیتم می‌تواند شامل تصویر، نام نویسنده، زیرعنوان، متن و لیبل‌ها باشد.--}}
                {{--                                </div>--}}

                {{--                                <x-admin.form.repeater--}}
                {{--                                    name="home_testimonial_items"--}}
                {{--                                    addLabel="افزودن تجربه کاربر"--}}
                {{--                                    emptyTitle="هنوز آیتمی ثبت نشده است"--}}
                {{--                                    emptyDescription="بعداً فیلدهای داخلی هر تجربه را در ریپیتر اضافه می‌کنیم."--}}
                {{--                                >--}}
                {{--                                    --}}{{-- shell repeater items --}}
                {{--                                </x-admin.form.repeater>--}}
                {{--                            </div>--}}
                {{--                        </x-admin.metabox>--}}
                {{--                    </div>--}}
                {{--                </x-admin.tabs.panel>--}}
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
