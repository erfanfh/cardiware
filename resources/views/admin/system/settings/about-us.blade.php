@extends('admin.layout')

@section('title', 'تنظیمات درباره ما')

@php
    $aboutUs = app(\App\Infrastructure\Services\Setting\Groups\AboutUsSettings::class)->values();
@endphp

@section('content')
    <x-admin.form action="{{ route('admin.settings.about-us.store') }}" method="put">
        <x-admin.page>
            <x-admin.form.validation-errors class="mb-6"/>

            <div class="space-y-6">
                <x-admin.metabox title="بخش ابتدایی و Hero">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                        <div class="space-y-5 lg:col-span-8">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <x-admin.field label="برچسب بالای عنوان">
                                    <x-admin.form.input name="hero_badge" :value="old('hero_badge', $aboutUs['hero_badge'])"/>
                                </x-admin.field>

                                <x-admin.field label="عنوان اصلی">
                                    <x-admin.form.input name="hero_title" :value="old('hero_title', $aboutUs['hero_title'])"/>
                                </x-admin.field>
                            </div>

                            <x-admin.field label="توضیحات Hero">
                                <x-admin.form.editor name="hero_description" :value="old('hero_description', $aboutUs['hero_description'])"/>
                            </x-admin.field>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <x-admin.field label="متن دکمه پروژه‌ها">
                                    <x-admin.form.input name="hero_primary_button_title" :value="old('hero_primary_button_title', $aboutUs['hero_primary_button_title'])"/>
                                </x-admin.field>

                                <x-admin.field label="متن دکمه ارتباط با ما">
                                    <x-admin.form.input name="hero_secondary_button_title" :value="old('hero_secondary_button_title', $aboutUs['hero_secondary_button_title'])"/>
                                </x-admin.field>
                            </div>
                        </div>

                        <x-admin.field
                            class="lg:col-span-4"
                            label="تصویر پس‌زمینه Hero"
                            hint="اگر تصویری انتخاب نشود، تصویر شاخص خود صفحه درباره ما استفاده می‌شود."
                        >
                            <x-admin.form.media-picker
                                name="hero_image"
                                layout="vertical"
                                :value="[
                                    'id' => old('hero_image.id', $aboutUs['hero_image']->id),
                                    'path' => old('hero_image.path', $aboutUs['hero_image']->path),
                                ]"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="عنوان کارت‌های آمار زنده">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <x-admin.field label="پروژه‌های در حال توسعه">
                            <x-admin.form.input name="stats_in_progress_title" :value="old('stats_in_progress_title', $aboutUs['stats_in_progress_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="پروژه‌های تکمیل‌شده">
                            <x-admin.form.input name="stats_completed_title" :value="old('stats_completed_title', $aboutUs['stats_completed_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="محصولات منتشرشده">
                            <x-admin.form.input name="stats_products_title" :value="old('stats_products_title', $aboutUs['stats_products_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="کاربران فعال">
                            <x-admin.form.input name="stats_users_title" :value="old('stats_users_title', $aboutUs['stats_users_title'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ماموریت ما">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <x-admin.field label="عنوان باکس">
                            <x-admin.form.input name="our_mission_box_title" :value="old('our_mission_box_title', $aboutUs['our_mission_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field class="lg:col-span-2" label="توضیحات">
                            <x-admin.form.editor name="our_mission_description" :value="old('our_mission_description', $aboutUs['our_mission_description'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="چه چیزهایی می‌سازیم؟">
                    <div class="space-y-5">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="what_we_build_box_title" :value="old('what_we_build_box_title', $aboutUs['what_we_build_box_title'])"/>
                        </x-admin.field>

                        <x-admin.form.repeater
                            :items="old('what_we_build_items', $aboutUs['what_we_build_items'])"
                            name="what_we_build_items"
                            title="حوزه‌های فعالیت"
                            addText="افزودن حوزه فعالیت"
                            emptyText="هنوز موردی ثبت نشده است."
                            headingKey="title"
                            headingPlaceholder="حوزه بدون عنوان"
                            :defaultItem="[
                                'title' => '',
                                'subtitle' => '',
                                'url' => '',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <x-admin.field label="عنوان">
                                    <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                </x-admin.field>

                                <x-admin.field label="لینک">
                                    <x-admin.form.input
                                        x-model="it.url"
                                        x-bind:name="field(i, 'url')"
                                        dir="ltr"
                                        placeholder="https://... یا /products"
                                    />
                                </x-admin.field>

                                <x-admin.field class="lg:col-span-2" label="توضیح کوتاه">
                                    <x-admin.form.textarea rows="2" x-model="it.subtitle" x-bind:name="field(i, 'subtitle')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="پروژه‌های Cardiware">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="projects_box_title" :value="old('projects_box_title', $aboutUs['projects_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه مشاهده همه">
                            <x-admin.form.input name="projects_button_title" :value="old('projects_button_title', $aboutUs['projects_button_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="تعداد پروژه نمایشی" hint="بین ۱ تا ۶ مورد نمایش داده می‌شود.">
                            <x-admin.form.input
                                type="number"
                                name="projects_count"
                                min="1"
                                max="6"
                                :value="old('projects_count', $aboutUs['projects_count'])"
                            />
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2 xl:col-span-4" label="توضیحات بخش">
                            <x-admin.form.textarea
                                name="projects_box_description"
                                rows="2"
                                :value="old('projects_box_description', $aboutUs['projects_box_description'])"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="تیم Cardiware">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="our_team_box_title" :value="old('our_team_box_title', $aboutUs['our_team_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="تعداد اعضای نمایشی" hint="اعضا از بخش مدیریت اعضای تیم خوانده می‌شوند؛ بین ۱ تا ۱۲ نفر.">
                            <x-admin.form.input
                                type="number"
                                name="team_members_count"
                                min="1"
                                max="12"
                                :value="old('team_members_count', $aboutUs['team_members_count'])"
                            />
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2 xl:col-span-4" label="توضیحات بخش">
                            <x-admin.form.textarea
                                name="our_team_description"
                                rows="2"
                                :value="old('our_team_description', $aboutUs['our_team_description'])"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="سوالات متداول">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="faqs_box_title" :value="old('faqs_box_title', $aboutUs['faqs_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="توضیحات بخش">
                            <x-admin.form.textarea name="faqs_description" rows="2" :value="old('faqs_description', $aboutUs['faqs_description'])"/>
                        </x-admin.field>
                    </div>

                    <div class="mt-5">
                        <x-admin.form.repeater
                            :items="old('faqs_items', $aboutUs['faqs_items'])"
                            name="faqs_items"
                            title="سوال و جواب‌ها"
                            addText="افزودن سوال و جواب"
                            emptyText="هنوز سوالی ثبت نشده است."
                            headingKey="title"
                            headingPlaceholder="سوال بدون عنوان"
                            :defaultItem="[
                                'title' => '',
                                'answer' => '',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-4">
                                <x-admin.field label="سوال">
                                    <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                </x-admin.field>

                                <x-admin.field label="پاسخ">
                                    <x-admin.form.textarea rows="3" x-model="it.answer" x-bind:name="field(i, 'answer')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="دعوت به ارتباط و پشتیبانی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="contact_us_box_title" :value="old('contact_us_box_title', $aboutUs['contact_us_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه پشتیبانی">
                            <x-admin.form.input name="contact_us_button_title" :value="old('contact_us_button_title', $aboutUs['contact_us_button_title'])"/>
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2" label="توضیحات">
                            <x-admin.form.textarea
                                name="contact_us_description"
                                rows="3"
                                :value="old('contact_us_description', $aboutUs['contact_us_description'])"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="راه‌های ارتباطی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field class="md:col-span-2" label="عنوان باکس">
                            <x-admin.form.input name="contact_box_title" :value="old('contact_box_title', $aboutUs['contact_box_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="تلگرام" hint="نام کاربری یا آدرس کامل را وارد کنید.">
                            <x-admin.form.input name="contact_telegram" dir="ltr" :value="old('contact_telegram', $aboutUs['contact_telegram'])"/>
                        </x-admin.field>

                        <x-admin.field label="اینستاگرام" hint="نام کاربری یا آدرس کامل را وارد کنید.">
                            <x-admin.form.input name="contact_instagram" dir="ltr" :value="old('contact_instagram', $aboutUs['contact_instagram'])"/>
                        </x-admin.field>

                        <x-admin.field label="ایمیل">
                            <x-admin.form.input type="email" name="contact_email" dir="ltr" :value="old('contact_email', $aboutUs['contact_email'])"/>
                        </x-admin.field>

                        <x-admin.field label="شماره تماس">
                            <x-admin.form.input type="tel" name="contact_phone" dir="ltr" :value="old('contact_phone', $aboutUs['contact_phone'])"/>
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2" label="ساعات پاسخ‌گویی">
                            <x-admin.form.input name="contact_hour" :value="old('contact_hour', $aboutUs['contact_hour'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
