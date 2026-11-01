@extends('admin.layout')

@section('title', 'تنظیمات فوتر')

@section('content')
    <x-admin.form action="{{ route('admin.settings.footer.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="هیروباکس آمار فوتر">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            label="تصویر پس‌زمینه آمار"
                        >
                            <x-admin.form.media-picker
                                name="stats_bg_image"
                                :value="[
                                    'id' => old('stats_bg_image.id', get_setting('footer', 'stats_bg_image')->id),
                                    'path' => old('stats_bg_image.path', get_setting('footer', 'stats_bg_image')->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="انیمیشن آمار (شمارش عددی + جاروی نورانی)"
                        >
                            <x-admin.form.switch
                                name="stats_animation_enabled"
                                :checked="old('stats_animation_enabled', get_setting('footer', 'stats_animation_enabled', 1))"
                            />
                        </x-admin.field>
                    </div>

                    <div class="text-xs text-slate-500">
                        آمار نمایش‌داده‌شده: تعداد کاربران، تیکت‌ها، نوشته‌های منتشرشده و محصولات فعال فروشگاه — به‌صورت خودکار از دیتابیس محاسبه می‌شود.
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="هویت و متن‌های فوتر">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            label="لوگوی فوتر"
                        >
                            <x-admin.form.media-picker
                                name="logo"
                                :value="[
                                    'id' => old('logo.id', get_setting('footer', 'logo')->id),
                                    'path' => old('logo.path', get_setting('footer', 'logo')->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن درباره ما"
                        >
                            <x-admin.form.textarea
                                name="about"
                                rows="5"
                                value="{{ get_setting('footer', 'about') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-4"
                            label="متن کپی‌رایت"
                        >
                            <x-admin.form.input
                                name="copyright"
                                value="{{ get_setting('footer', 'copyright') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="عنوان باکس هدف و کیفیت"
                        >
                            <x-admin.form.input
                                name="quality_title"
                                value="{{ get_setting('footer', 'quality_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس هدف و کیفیت"
                        >
                            <x-admin.form.textarea
                                name="quality_text"
                                rows="4"
                                value="{{ get_setting('footer', 'quality_text') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="عنوان باکس تعهد ما"
                        >
                            <x-admin.form.input
                                name="commitment_title"
                                value="{{ get_setting('footer', 'commitment_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس تعهد ما"
                        >
                            <x-admin.form.textarea
                                name="commitment_text"
                                rows="4"
                                value="{{ get_setting('footer', 'commitment_text') }}"
                            />
                        </x-admin.field>

                        <div class="flex flex-col gap-4">
                            <x-admin.field
                                label="عنوان باکس فروشگاه"
                            >
                                <x-admin.form.input
                                    name="store_title"
                                    value="{{ get_setting('footer', 'store_title') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="لینک فروشگاه"
                            >
                                <x-admin.form.input
                                    name="store_url"
                                    value="{{ get_setting('footer', 'store_url') }}"
                                    class="direction-ltr"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس فروشگاه"
                        >
                            <x-admin.form.textarea
                                name="store_text"
                                rows="4"
                                value="{{ get_setting('footer', 'store_text') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 1 - ارتباط با ما">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column1_title"
                                value="{{ get_setting('footer', 'column1_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس ایمیل"
                        >
                            <x-admin.form.input
                                name="email"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'email') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="شماره تماس"
                        >
                            <x-admin.form.input
                                name="phone"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'phone') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی تلگرام"
                        >
                            <x-admin.form.input
                                name="telegram"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'telegram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی اینستاگرام"
                        >
                            <x-admin.form.input
                                name="instagram"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'instagram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی ایتا"
                        >
                            <x-admin.form.input
                                name="eitaa"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'eitaa') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی بله"
                        >
                            <x-admin.form.input
                                name="bale"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'bale') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی روبیکا"
                        >
                            <x-admin.form.input
                                name="rubika"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'rubika') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی سروش"
                        >
                            <x-admin.form.input
                                name="soroush"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'soroush') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی آپارات"
                        >
                            <x-admin.form.input
                                name="aparat"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'aparat') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 1"
                        >
                            <x-admin.form.input
                                name="address_1"
                                value="{{ get_setting('footer', 'address_1') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 2"
                        >
                            <x-admin.form.input
                                name="address_2"
                                value="{{ get_setting('footer', 'address_2') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 3"
                        >
                            <x-admin.form.input
                                name="address_3"
                                value="{{ get_setting('footer', 'address_3') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 4"
                        >
                            <x-admin.form.input
                                name="address_4"
                                value="{{ get_setting('footer', 'address_4') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="باکس تیتر اخبار فوتر">
                    <div class="space-y-5">
                        <p class="text-sm leading-7 text-slate-500">
                            اگر تیتر دستی ثبت نشده باشد، تازه‌ترین نوشته‌های منتشرشده سایت نمایش داده می‌شوند.
                            لینک هر تیتر دستی اختیاری است. حداکثر ۸ تیتر می‌توانید ثبت کنید.
                        </p>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <x-admin.field label="نمایش باکس اخبار" class="switch-field">
                                <x-admin.form.switch
                                    name="news_enabled"
                                    :checked="old('news_enabled', get_setting('footer', 'news_enabled', 1))"
                                />
                            </x-admin.field>

                            <x-admin.field label="عنوان باکس">
                                <x-admin.form.input
                                    name="news_title"
                                    maxlength="100"
                                    :value="old('news_title', get_setting('footer', 'news_title', 'تیتر اخبار'))"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.form.repeater
                            :items="old('news_items', get_setting('footer', 'news_items', []))"
                            name="news_items"
                            title="تیترهای دستی"
                            addText="افزودن تیتر"
                            emptyText="تیتر دستی ثبت نشده است؛ تازه‌ترین نوشته‌ها نمایش داده می‌شوند."
                            :defaultItem="['title' => '', 'url' => '']"
                        >
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <x-admin.field label="تیتر" required>
                                    <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"
                                                        maxlength="180"/>
                                </x-admin.field>

                                <x-admin.field label="لینک (اختیاری)">
                                    <x-admin.form.input
                                        x-model="it.url"
                                        x-bind:name="field(i, 'url')"
                                        class="direction-ltr"
                                        dir="ltr"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 2 - خدمات و محصولات">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column2_title"
                                value="{{ get_setting('footer', 'column2_title') }}"
                            />
                        </x-admin.field>

                        <div class="mt-5 lg:col-span-4">
                            <x-admin.form.repeater
                                :items="get_setting('footer', 'column2_items')"
                                name="column2_items"
                                title="آیتم ها"
                                addText="افزودن آیتم"
                                :defaultItem="[
                                    'title' => '',
                                    'url' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-6">
                                        <x-admin.field label="عنوان" required>
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-6">
                                        <x-admin.field label="لینک" required>
                                            <x-admin.form.input x-model="it.url"
                                                                x-bind:name="field(i, 'url')"
                                                                class="direction-ltr"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 3 - دسترسی سریع">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column3_title"
                                value="{{ get_setting('footer', 'column3_title') }}"
                            />
                        </x-admin.field>

                        <div class="mt-5 lg:col-span-4">
                            <x-admin.form.repeater
                                :items="get_setting('footer', 'column3_items')"
                                name="column3_items"
                                title="آیتم ها"
                                addText="افزودن آیتم"
                                :defaultItem="[
                                    'title' => '',
                                    'url' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-6">
                                        <x-admin.field label="عنوان" required>
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-6">
                                        <x-admin.field label="لینک" required>
                                            <x-admin.form.input x-model="it.url"
                                                                x-bind:name="field(i, 'url')"
                                                                class="direction-ltr"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 4 - نماد ها">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column4_title"
                                value="{{ get_setting('footer', 'column4_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="تصویر نمادها (حالت روشن)"
                        >
                            <x-admin.form.media-picker
                                name="column4_image"
                                :value="[
                                    'id' => old('column4_image.id', get_setting('footer', 'column4_image')->id),
                                    'path' => old('column4_image.path', get_setting('footer', 'column4_image')->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="تصویر نمادها (حالت تاریک)"
                        >
                            <x-admin.form.media-picker
                                name="column4_image_dark"
                                :value="[
                                    'id' => old('column4_image_dark.id', get_setting('footer', 'column4_image_dark')->id),
                                    'path' => old('column4_image_dark.path', get_setting('footer', 'column4_image_dark')->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-4"
                            label="کد نمادها"
                        >
                            <x-admin.form.textarea
                                name="namad_code"
                                value="{!! get_setting('footer', 'namad_code') !!}"
                                class="direction-ltr"
                                rows="6"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
