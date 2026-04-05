@extends('admin.layout')

@section('title', 'تنظیمات درباره ما')

@section('content')
    <x-admin.form action="{{ route('admin.settings.about-us.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="اطلاعات باکس هدر">
                    <div class="grid grid-cols-1 gap-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <x-admin.field
                                label="عنوان بج"
                            >
                                <x-admin.form.input
                                    name="hero_badge"
                                    value="{{ get_setting('aboutUs', 'hero_badge') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="عنوان اصلی"
                            >
                                <x-admin.form.input
                                    name="hero_title"
                                    value="{{ get_setting('aboutUs', 'hero_title') }}"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.field
                            label="توضیحات هیرو"
                        >
                            <x-admin.form.editor
                                name="hero_description"
                                value="{{ get_setting('aboutUs', 'hero_description') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس ماموریت ما">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="our_mission_box_title"
                                value="{{ get_setting('aboutUs', 'our_mission_box_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="توضیحات"
                        >
                            <x-admin.form.editor
                                name="our_mission_description"
                                value="{{ get_setting('aboutUs', 'our_mission_description') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس چه چیزهایی می‌سازیم؟">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="what_we_build_box_title"
                                value="{{ get_setting('aboutUs', 'what_we_build_box_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.repeater
                                :items="get_setting('aboutUs', 'what_we_build_items')"
                                name="what_we_build_items"
                                title="آیتم ها"
                                addText="افزودن آیتم"
                                :defaultItem="[
                                    'title' => '',
                                    'subtitle' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-6">
                                        <x-admin.field label="عنوان">
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-6">
                                        <x-admin.field label="زیرعنوان">
                                            <x-admin.form.input x-model="it.subtitle"
                                                                x-bind:name="field(i, 'subtitle')"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس پروژه‌های مودینگ‌وی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="projects_box_title"
                                value="{{ get_setting('aboutUs', 'projects_box_title') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس تیم مودینگ‌وی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="our_team_box_title"
                                value="{{ get_setting('aboutUs', 'our_team_box_title') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس سوالات رایج">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="faqs_box_title"
                                value="{{ get_setting('aboutUs', 'faqs_box_title') }}"
                            />
                        </x-admin.field>
                    </div>
                    <div class="grid grid-cols-1 gap-5 mt-3">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.repeater
                                :items="get_setting('aboutUs', 'faqs_items')"
                                name="faqs_items"
                                title="سوال و جواب ها"
                                addText="افزودن سوال و جواب"
                                :defaultItem="[
                                    'title' => '',
                                    'answer' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-12">
                                        <x-admin.field label="سوال">
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-12">
                                        <x-admin.field label="جواب">
                                            <x-admin.form.textarea rows="2" x-model="it.answer"
                                                                   x-bind:name="field(i, 'answer')"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس ارتباط با ما">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="contact_us_box_title"
                                value="{{ get_setting('aboutUs', 'contact_us_box_title') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اطلاعات باکس راه‌های ارتباطی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان باکس"
                        >
                            <x-admin.form.input
                                name="contact_box_title"
                                value="{{ get_setting('aboutUs', 'contact_box_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="آدرس تلگرام"
                        >
                            <x-admin.form.input
                                name="contact_telegram"
                                value="{{ get_setting('aboutUs', 'contact_telegram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="آدرس اینستاگرام"
                        >
                            <x-admin.form.input
                                name="contact_instagram"
                                value="{{ get_setting('aboutUs', 'contact_instagram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="آدرس ایمیل"
                        >
                            <x-admin.form.input
                                name="contact_email"
                                value="{{ get_setting('aboutUs', 'contact_email') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="شماره تماس"
                        >
                            <x-admin.form.input
                                name="contact_phone"
                                value="{{ get_setting('aboutUs', 'contact_phone') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="ساعات پاسخ‌گویی"
                        >
                            <x-admin.form.input
                                name="contact_hour"
                                value="{{ get_setting('aboutUs', 'contact_hour') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
