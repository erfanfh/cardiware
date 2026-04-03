@extends('admin.layout')

@section('title', 'تنظیمات تبلیغات')

@section('content')
    <x-admin.form action="{{ route('admin.settings.ads.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'projects' => 'پروژه ها',
                ]"
                active="projects"
            >
                <x-admin.tabs.panel name="projects">
                    <div class="space-y-6">
                        <x-admin.metabox title="تبلیغات صفحات پروژه ها" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    تبلیغات صفحات پروژه ها را در این بخش مدیریت کنید.
                                </div>

                                <x-admin.form.repeater
                                    name="project_ads"
                                    :items="old('project_ads', get_setting('ads', 'project_ads') ?? [])"
                                    title="تبلیغات"
                                    addText="افزودن تبلیغ"
                                    emptyText="هنوز تبلیغی اضافه نشده است."
                                    headingKey="title"
                                    headingPlaceholder="تبلیغ بدون عنوان"
                                    :defaultItem="[
                                        'title' => '',
                                        'url' => '',
                                        'image_id' => null,
                                        'image_path' => null,
                                        'sort_order' => 0,
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
                                            label="آدرس"
                                        >
                                            <x-admin.form.input
                                                x-bind:name="field(i, 'url')"
                                                x-model="it.url"
                                            />
                                        </x-admin.field>

                                        <x-admin.field
                                            label="تصویر"
                                            class="lg:col-span-2"
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
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
