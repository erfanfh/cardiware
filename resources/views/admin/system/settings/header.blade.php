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
                    <div class="space-y-6" x-data="{ backgroundType: @js(old('announcement_background_type', get_setting('header', 'announcement_background_type') ?: (get_setting('header', 'announcement_background_image')?->path ? 'image' : 'color'))), pattern: @js(old('announcement_pattern', get_setting('header', 'announcement_pattern') ?: 'dots')) }">
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

                                <x-admin.field label="نوع پس‌زمینه" class="lg:col-span-2">
                                    <x-admin.form.select
                                        name="announcement_background_type"
                                        x-model="backgroundType"
                                        :value="old('announcement_background_type', get_setting('header', 'announcement_background_type') ?: (get_setting('header', 'announcement_background_image')?->path ? 'image' : 'color'))"
                                        :options="['color' => 'رنگ ساده', 'image' => 'تصویر', 'gradient' => 'گرادینت متحرک', 'pattern' => 'پترن متحرک']"
                                    />
                                </x-admin.field>

                                <x-admin.field
                                    label="تصویر بک گراند"
                                    x-show="backgroundType === 'image'"
                                    x-cloak
                                >
                                    <x-admin.form.media-picker
                                        name="announcement_background_image"
                                        :value="[
                                    'id' => old('announcement_background_image.id', get_setting('header', 'announcement_background_image')->id),
                                    'path' => old('announcement_background_image.path', get_setting('header', 'announcement_background_image')->path),
                                ]"
                                    />
                                </x-admin.field>

                                <div class="flex flex-wrap gap-5 lg:col-span-2">
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

                                <div class="grid grid-cols-1 gap-5 lg:col-span-2 lg:grid-cols-2" x-show="backgroundType === 'gradient'" x-cloak>
                                    <x-admin.field label="رنگ دوم گرادینت">
                                        <x-admin.form.colorpicker name="announcement_gradient_color" default="#7c3aed"
                                            :value="old('announcement_gradient_color', get_setting('header', 'announcement_gradient_color'))" />
                                    </x-admin.field>
                                    <x-admin.field label="زاویه گرادینت (درجه)">
                                        <x-admin.form.input name="announcement_gradient_angle" type="number" min="0" max="360"
                                            :value="old('announcement_gradient_angle', get_setting('header', 'announcement_gradient_angle') ?? 120)" />
                                    </x-admin.field>
                                    <p class="text-xs text-slate-500 lg:col-span-2">رنگ بک گراند، رنگ اول گرادینت است.</p>
                                </div>

                                <div class="grid grid-cols-1 gap-5 lg:col-span-2 lg:grid-cols-2" x-show="backgroundType === 'pattern'" x-cloak>
                                    <x-admin.field label="طرح پترن">
                                        <x-admin.form.select name="announcement_pattern" x-model="pattern"
                                            :value="old('announcement_pattern', get_setting('header', 'announcement_pattern') ?: 'dots')"
                                            :options="['dots' => 'نقطه‌ای', 'stripes' => 'خطوط مورب', 'grid' => 'شبکه‌ای', 'image' => 'تصویر دلخواه']" />
                                    </x-admin.field>
                                    <x-admin.field label="اندازه کاشی پترن (پیکسل)">
                                        <x-admin.form.input name="announcement_pattern_size" type="number" min="8" max="256"
                                            :value="old('announcement_pattern_size', get_setting('header', 'announcement_pattern_size') ?: 32)" />
                                    </x-admin.field>
                                    <x-admin.field label="رنگ پترن" x-show="pattern !== 'image'">
                                        <x-admin.form.colorpicker name="announcement_pattern_color" default="#ffffff26"
                                            :value="old('announcement_pattern_color', get_setting('header', 'announcement_pattern_color'))" />
                                    </x-admin.field>
                                    <x-admin.field label="تصویر پترن" x-show="pattern === 'image'">
                                        <x-admin.form.media-picker name="announcement_pattern_image"
                                            :value="[
                                                'id' => old('announcement_pattern_image.id', get_setting('header', 'announcement_pattern_image')->id),
                                                'path' => old('announcement_pattern_image.path', get_setting('header', 'announcement_pattern_image')->path),
                                            ]" />
                                    </x-admin.field>
                                    <p class="text-xs text-slate-500 lg:col-span-2">پترن روی رنگ بک گراند تکرار می‌شود. برای تصویر دلخواه، از طرح مربعی با لبه‌های پیوسته استفاده کنید.</p>
                                </div>

                                <x-admin.field label="مدت هر چرخه حرکت (ثانیه)" x-show="['gradient', 'pattern'].includes(backgroundType)" x-cloak>
                                    <x-admin.form.input name="announcement_animation_duration" type="number" min="2" max="120"
                                        :value="old('announcement_animation_duration', get_setting('header', 'announcement_animation_duration') ?: 12)" />
                                    <p class="mt-2 text-xs text-slate-500">عدد کمتر، حرکت سریع‌تر است. محدوده مجاز: ۲ تا ۱۲۰ ثانیه.</p>
                                </x-admin.field>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <input type="hidden" name="active_tab" id="active_tab_input" value="">

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
