@extends('admin.layout')

@section('title', 'تنظیمات هدر')

@section('content')
    <x-admin.form action="{{ route('admin.settings.header.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'general' => 'عمومی',
                    'announcement' => 'باکس اطلاعیه',
                    'notification' => 'اعلانات',
                ]"
                active="general"
            >
                <x-admin.tabs.panel name="general">
                    <div class="space-y-6">
                        <x-admin.metabox title="لوگو">
                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                <x-admin.field
                                    label="لوگوی هدر"
                                >
                                    <x-admin.form.media-picker
                                        mode="field"
                                        name="logo"
                                        :value="[
                                            'id' => old('logo.id', get_setting('header', 'logo')->id),
                                            'path' => old('logo.path', get_setting('header', 'logo')->path),
                                        ]"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>

                        <x-admin.metabox title="جستجو">
                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                <x-admin.field
                                    label="فعال بودن جستجوی Ajax"
                                    hint="در صورت فعال بودن، نتایج جستجو به‌صورت زنده نمایش داده می‌شوند."
                                >
                                    <x-admin.form.switch
                                        name="ajax_search"
                                        :checked="get_setting('header', 'ajax_search')"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="notification">
                    <div class="space-y-6">
                        <x-admin.metabox title="اطلاعات اعلانات" :collapsible="false">
                            <div class="grid grid-cols-1">
                                <x-admin.form.repeater
                                    name="notifications"
                                    :items="old('notifications', get_setting('header', 'notifications') ?? [])"
                                    title="اعلانات"
                                    addText="افزودن اعلان"
                                    emptyText="هنوز اعلانی اضافه نشده است."
                                    headingKey="title"
                                    headingPlaceholder="اعلان بدون عنوان"
                                    :defaultItem="[
                                        'title' => '',
                                        'subtitle' => '',
                                        'url' => '',
                                        'sort_order' => 0,
                                    ]"
                                >
                                    <div class="grid grid-cols-2 gap-5">
                                        <x-admin.field
                                            label="متن اعلان"
                                            class="col-span-2"
                                        >
                                            <x-admin.form.input
                                                x-bind:name="field(i, 'title')"
                                                x-model="it.title"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="زیرعنوان اعلان">
                                            <x-admin.form.input
                                                x-bind:name="field(i, 'subtitle')"
                                                x-model="it.subtitle"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="لینک اعلان">
                                            <x-admin.form.input
                                                x-bind:name="field(i, 'url')"
                                                x-model="it.url"
                                            />
                                        </x-admin.field>
                                    </div>
                                </x-admin.form.repeater>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="announcement">
                    <div class="space-y-6">
                        <x-admin.metabox title="باکس اطلاعیه">
                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                <div class="flex gap-5 lg:col-span-2">
                                    <x-admin.field
                                        label="فعال سازی"
                                    >
                                        <x-admin.form.switch
                                            name="announcement_active"
                                            :checked="get_setting('header', 'announcement_active')"
                                        />
                                    </x-admin.field>

                                    <x-admin.field
                                        label="متن اطلاعیه"
                                        class="grow"
                                    >
                                        <x-admin.form.input
                                            name="announcement_text"
                                            value="{{ get_setting('header', 'announcement_text') }}"
                                        />
                                    </x-admin.field>
                                </div>

                                <x-admin.field
                                    label="متن دکمه"
                                >
                                    <x-admin.form.input
                                        name="announcement_button_text"
                                        value="{{ get_setting('header', 'announcement_button_text') }}"
                                    />
                                </x-admin.field>

                                <x-admin.field
                                    label="لینک دکمه"
                                >
                                    <x-admin.form.input
                                        name="announcement_button_url"
                                        class="direction-ltr"
                                        value="{{ get_setting('header', 'announcement_button_url') }}"
                                    />
                                </x-admin.field>

                                <x-admin.field
                                    label="تصویر بک گراند"
                                >
                                    <x-admin.form.media-picker
                                        name="announcement_background_image"
                                        :value="[
                                    'id' => old('announcement_background_image.id', get_setting('header', 'announcement_background_image')->id),
                                    'path' => old('announcement_background_image.path', get_setting('header', 'announcement_background_image')->path),
                                ]"
                                    />
                                </x-admin.field>

                                <div class="flex flex-wrap gap-5">
                                    <x-admin.field
                                        label="رنگ بک گراند"
                                    >
                                        <x-admin.form.colorpicker
                                            name="announcement_background_color"
                                            default="#ea580c"
                                            value="{{ get_setting('header', 'announcement_background_color') }}"
                                        />
                                    </x-admin.field>

                                    <x-admin.field
                                        label="رنگ متن ها"
                                    >
                                        <x-admin.form.colorpicker
                                            name="announcement_text_color"
                                            default="#fff"
                                            value="{{ get_setting('header', 'announcement_text_color') }}"
                                        />
                                    </x-admin.field>
                                </div>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
